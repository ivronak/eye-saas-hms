import 'dart:convert';
import 'package:http/http.dart' as http;
import '../config/app_config.dart';

class ForgotPasswordResult {
  final bool success;
  final String message;
  final String? otp;
  final String? resetToken;
  final int resendIn;

  const ForgotPasswordResult({
    required this.success,
    required this.message,
    this.otp,
    this.resetToken,
    this.resendIn = 0,
  });
}

/// Forgot-password flow: email -> OTP (shown on screen, not emailed) -> new password.
class ForgotPasswordService {
  static const _headers = {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };

  Future<ForgotPasswordResult> _post(String path, Map<String, dynamic> body) async {
    try {
      final response = await http
          .post(Uri.parse('${AppConfig.hospitalApiUrl}$path'),
              headers: _headers, body: jsonEncode(body))
          .timeout(AppConfig.requestTimeout);
      final json = jsonDecode(response.body) as Map<String, dynamic>;
      final data = json['data'] is Map ? json['data'] as Map : const {};
      var message = (json['message'] as String?) ?? 'Something went wrong.';
      if (json['errors'] is Map && (json['errors'] as Map).isNotEmpty) {
        final first = (json['errors'] as Map).values.first;
        if (first is List && first.isNotEmpty) message = first.first.toString();
      }
      return ForgotPasswordResult(
        success: response.statusCode == 200 && json['success'] == true,
        message: message,
        otp: data['otp']?.toString(),
        resetToken: data['reset_token']?.toString(),
        resendIn: (data['resend_in'] as num?)?.toInt() ?? 0,
      );
    } catch (_) {
      return const ForgotPasswordResult(
        success: false,
        message: 'Could not connect. Please check your internet and try again.',
      );
    }
  }

  /// Finds the hospital for this email, then requests an OTP.
  /// Also used for "Resend OTP".
  Future<ForgotPasswordResult> requestOtp(String email) async {
    try {
      final uri = Uri.parse('${AppConfig.apiBaseUrl}/find-hospital')
          .replace(queryParameters: {'email': email});
      final response =
          await http.get(uri, headers: _headers).timeout(AppConfig.requestTimeout);
      final body = jsonDecode(response.body) as Map<String, dynamic>;
      if (response.statusCode != 200 || body['success'] != true) {
        return const ForgotPasswordResult(
            success: false, message: 'No account found with this email.');
      }
      AppConfig.slug = (body['data'] as Map)['slug'] as String;
    } catch (_) {
      return const ForgotPasswordResult(
        success: false,
        message: 'Could not connect. Please check your internet and try again.',
      );
    }
    return _post('/auth/forgot-password', {'email': email});
  }

  Future<ForgotPasswordResult> verifyOtp(String email, String otp) =>
      _post('/auth/verify-otp', {'email': email, 'otp': otp});

  Future<ForgotPasswordResult> resetPassword(
          String email, String resetToken, String password, String confirm) =>
      _post('/auth/reset-password', {
        'email': email,
        'reset_token': resetToken,
        'password': password,
        'password_confirmation': confirm,
      });
}
