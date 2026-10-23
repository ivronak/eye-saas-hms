import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import '../constants/app_colors.dart';
import '../services/forgot_password_service.dart';

enum _Step { email, otp, password }

class ForgotPasswordScreen extends StatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  State<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends State<ForgotPasswordScreen> {
  final _service = ForgotPasswordService();
  final _emailCtrl = TextEditingController();
  final _otpCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _confirmCtrl = TextEditingController();

  _Step _step = _Step.email;
  bool _loading = false;
  String? _error;
  String? _shownOtp;
  String? _resetToken;
  int _secondsLeft = 0;
  Timer? _timer;

  @override
  void dispose() {
    _timer?.cancel();
    _emailCtrl.dispose();
    _otpCtrl.dispose();
    _passCtrl.dispose();
    _confirmCtrl.dispose();
    super.dispose();
  }

  void _startTimer(int seconds) {
    _timer?.cancel();
    setState(() => _secondsLeft = seconds);
    _timer = Timer.periodic(const Duration(seconds: 1), (t) {
      if (_secondsLeft <= 1) {
        t.cancel();
      }
      if (mounted) setState(() => _secondsLeft = _secondsLeft > 0 ? _secondsLeft - 1 : 0);
    });
  }

  Future<void> _requestOtp({bool resend = false}) async {
    final email = _emailCtrl.text.trim();
    if (email.isEmpty || !email.contains('@')) {
      setState(() => _error = 'Please enter a valid email address.');
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
    });
    final r = await _service.requestOtp(email);
    if (!mounted) return;
    setState(() => _loading = false);
    if (!r.success) {
      if (r.resendIn > 0) _startTimer(r.resendIn);
      setState(() => _error = r.message);
      return;
    }
    setState(() {
      _shownOtp = r.otp;
      _step = _Step.otp;
      _otpCtrl.clear();
    });
    _startTimer(r.resendIn > 0 ? r.resendIn : 30);
  }

  Future<void> _verifyOtp() async {
    final otp = _otpCtrl.text.trim();
    if (otp.length != 6) {
      setState(() => _error = 'Please enter the 6-digit OTP.');
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
    });
    final r = await _service.verifyOtp(_emailCtrl.text.trim(), otp);
    if (!mounted) return;
    setState(() => _loading = false);
    if (!r.success || r.resetToken == null) {
      setState(() => _error = r.message);
      return;
    }
    _timer?.cancel();
    setState(() {
      _resetToken = r.resetToken;
      _step = _Step.password;
    });
  }

  Future<void> _resetPassword() async {
    if (_passCtrl.text.length < 8) {
      setState(() => _error = 'Password must be at least 8 characters.');
      return;
    }
    if (_passCtrl.text != _confirmCtrl.text) {
      setState(() => _error = 'Passwords do not match.');
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
    });
    final r = await _service.resetPassword(_emailCtrl.text.trim(),
        _resetToken ?? '', _passCtrl.text, _confirmCtrl.text);
    if (!mounted) return;
    setState(() => _loading = false);
    if (!r.success) {
      setState(() => _error = r.message);
      return;
    }
    ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Password reset. Please sign in.')));
    Navigator.of(context).pop();
  }

  @override
  Widget build(BuildContext context) {
    final title = switch (_step) {
      _Step.email => 'Forgot Password',
      _Step.otp => 'Verify OTP',
      _Step.password => 'New Password',
    };
    return Scaffold(
      appBar: AppBar(title: Text(title)),
      body: SafeArea(
        child: Center(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(24),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 420),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  if (_error != null)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 16),
                      child: Text(_error!,
                          style: const TextStyle(color: Colors.red)),
                    ),
                  ..._buildStep(),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }

  List<Widget> _buildStep() {
    switch (_step) {
      case _Step.email:
        return [
          const Text('Enter your registered email to get an OTP.'),
          const SizedBox(height: 16),
          TextField(
            controller: _emailCtrl,
            keyboardType: TextInputType.emailAddress,
            decoration: const InputDecoration(
                labelText: 'Email', border: OutlineInputBorder()),
            onSubmitted: (_) => _requestOtp(),
          ),
          const SizedBox(height: 20),
          _button('Continue', _requestOtp),
        ];
      case _Step.otp:
        return [
          Text('Enter the OTP for ${_emailCtrl.text.trim()}'),
          if (_shownOtp != null) ...[
            const SizedBox(height: 12),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppColors.blue.withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Text(_shownOtp!,
                  textAlign: TextAlign.center,
                  style: const TextStyle(
                      fontSize: 24, fontWeight: FontWeight.w800, letterSpacing: 8)),
            ),
          ],
          const SizedBox(height: 16),
          TextField(
            controller: _otpCtrl,
            keyboardType: TextInputType.number,
            maxLength: 6,
            inputFormatters: [FilteringTextInputFormatter.digitsOnly],
            decoration: const InputDecoration(
                labelText: 'OTP', border: OutlineInputBorder()),
            onSubmitted: (_) => _verifyOtp(),
          ),
          const SizedBox(height: 8),
          _button('Verify OTP', _verifyOtp),
          const SizedBox(height: 8),
          TextButton(
            onPressed: (_secondsLeft > 0 || _loading)
                ? null
                : () => _requestOtp(resend: true),
            child: Text(_secondsLeft > 0
                ? 'Resend OTP in ${_secondsLeft}s'
                : 'Resend OTP'),
          ),
        ];
      case _Step.password:
        return [
          TextField(
            controller: _passCtrl,
            obscureText: true,
            decoration: const InputDecoration(
                labelText: 'New password', border: OutlineInputBorder()),
          ),
          const SizedBox(height: 16),
          TextField(
            controller: _confirmCtrl,
            obscureText: true,
            decoration: const InputDecoration(
                labelText: 'Confirm password', border: OutlineInputBorder()),
            onSubmitted: (_) => _resetPassword(),
          ),
          const SizedBox(height: 20),
          _button('Reset Password', _resetPassword),
        ];
    }
  }

  Widget _button(String label, VoidCallback onPressed) {
    return SizedBox(
      height: 48,
      child: ElevatedButton(
        style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.blue, foregroundColor: Colors.white),
        onPressed: _loading ? null : onPressed,
        child: _loading
            ? const SizedBox(
                width: 20,
                height: 20,
                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
            : Text(label),
      ),
    );
  }
}
