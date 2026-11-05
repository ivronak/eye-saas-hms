import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../constants/app_colors.dart';
import '../constants/app_radius.dart';
import '../services/auth_service.dart';

/// "Forgot Password?" from the login screen. Mirrors the web flow: the server
/// e-mails a reset link, the new password is chosen on the web reset page,
/// then the user signs in here again.
Future<void> showForgotPasswordSheet(BuildContext context, {String initialLogin = ''}) {
  return showModalBottomSheet<void>(
    context: context,
    isScrollControlled: true,
    useSafeArea: true,
    backgroundColor: Colors.white,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(AppRadius.xxl)),
    ),
    builder: (_) => _ForgotPasswordSheet(initialLogin: initialLogin),
  );
}

class _ForgotPasswordSheet extends StatefulWidget {
  final String initialLogin;
  const _ForgotPasswordSheet({required this.initialLogin});

  @override
  State<_ForgotPasswordSheet> createState() => _ForgotPasswordSheetState();
}

class _ForgotPasswordSheetState extends State<_ForgotPasswordSheet> {
  final _formKey = GlobalKey<FormState>();
  late final TextEditingController _loginCtrl = TextEditingController(text: widget.initialLogin);
  bool _sending = false;
  String? _error;
  String? _sentTo;

  @override
  void dispose() {
    _loginCtrl.dispose();
    super.dispose();
  }

  Future<void> _send() async {
    if (!_formKey.currentState!.validate()) return;
    FocusScope.of(context).unfocus();
    setState(() {
      _sending = true;
      _error = null;
    });
    final result = await AuthService.instance.forgotPassword(_loginCtrl.text);
    if (!mounted) return;
    setState(() {
      _sending = false;
      if (result.success) {
        _sentTo = result.email ?? 'your registered email';
      } else {
        _error = result.message;
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final sent = _sentTo != null;
    return Padding(
      padding: EdgeInsets.fromLTRB(24, 12, 24, 24 + MediaQuery.viewInsetsOf(context).bottom),
      child: SingleChildScrollView(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Center(
              child: Container(
                width: 40,
                height: 4,
                decoration: BoxDecoration(color: AppColors.primaryA22, borderRadius: BorderRadius.circular(2)),
              ),
            ),
            const SizedBox(height: 20),
            Icon(
              sent ? Icons.mark_email_read_outlined : Icons.lock_reset_rounded,
              color: AppColors.blue,
              size: 32,
            ),
            const SizedBox(height: 12),
            Text(
              sent ? 'Check your email' : 'Forgot password?',
              style: GoogleFonts.poppins(fontSize: 20, fontWeight: FontWeight.w900, color: AppColors.primary),
            ),
            const SizedBox(height: 6),
            if (sent) ..._sentContent() else ..._formContent(),
          ],
        ),
      ),
    );
  }

  List<Widget> _formContent() {
    return [
      Text(
        'Enter the email or phone number you sign in with. We will email you a link to reset your password.',
        style: GoogleFonts.poppins(fontSize: 13, fontWeight: FontWeight.w500, height: 1.5, color: AppColors.primaryA40),
      ),
      const SizedBox(height: 18),
      if (_error != null) ...[
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          decoration: BoxDecoration(
            color: const Color(0xFFFFDAD6),
            borderRadius: BorderRadius.circular(AppRadius.md),
          ),
          child: Row(
            children: [
              const Icon(Icons.error_outline_rounded, color: Color(0xFFBA1A1A), size: 20),
              const SizedBox(width: 10),
              Expanded(
                child: Text(
                  _error!,
                  style: GoogleFonts.poppins(fontSize: 12, fontWeight: FontWeight.w600, color: const Color(0xFFBA1A1A)),
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: 14),
      ],
      Form(
        key: _formKey,
        child: TextFormField(
          controller: _loginCtrl,
          enabled: !_sending,
          keyboardType: TextInputType.emailAddress,
          textInputAction: TextInputAction.done,
          onFieldSubmitted: (_) => _send(),
          validator: (v) => (v == null || v.trim().isEmpty) ? 'Please enter your email or phone' : null,
          style: GoogleFonts.poppins(fontSize: 14, fontWeight: FontWeight.w600, color: AppColors.primary),
          decoration: InputDecoration(
            filled: true,
            fillColor: AppColors.background,
            hintText: 'Email or phone',
            hintStyle: GoogleFonts.poppins(fontSize: 14, fontWeight: FontWeight.w500, color: AppColors.primaryA35),
            prefixIcon: const Icon(Icons.mail_outline_rounded, color: AppColors.blue, size: 20),
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(AppRadius.lg), borderSide: BorderSide.none),
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 16),
          ),
        ),
      ),
      const SizedBox(height: 18),
      SizedBox(
        width: double.infinity,
        height: 52,
        child: ElevatedButton(
          onPressed: _sending ? null : _send,
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.primary,
            foregroundColor: Colors.white,
            disabledBackgroundColor: AppColors.primary.withValues(alpha: 0.6),
            elevation: 0,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.lg)),
          ),
          child: _sending
              ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
              : Text('Send reset link', style: GoogleFonts.poppins(fontSize: 14, fontWeight: FontWeight.w800)),
        ),
      ),
    ];
  }

  List<Widget> _sentContent() {
    return [
      Text(
        'A password reset link was sent to $_sentTo.',
        style: GoogleFonts.poppins(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.primary),
      ),
      const SizedBox(height: 10),
      Text(
        'Open the email and tap the link. It opens in your browser, where you set a new password. '
        'Then come back here and sign in with it. The link is valid for 60 minutes. '
        'If you do not see the email, check your spam folder.',
        style: GoogleFonts.poppins(fontSize: 13, fontWeight: FontWeight.w500, height: 1.5, color: AppColors.primaryA40),
      ),
      const SizedBox(height: 20),
      SizedBox(
        width: double.infinity,
        height: 52,
        child: ElevatedButton(
          onPressed: () => Navigator.of(context).pop(),
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.primary,
            foregroundColor: Colors.white,
            elevation: 0,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.lg)),
          ),
          child: Text('Back to sign in', style: GoogleFonts.poppins(fontSize: 14, fontWeight: FontWeight.w800)),
        ),
      ),
    ];
  }
}
