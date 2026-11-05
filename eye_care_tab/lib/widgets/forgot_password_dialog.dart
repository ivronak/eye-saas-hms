import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import '../constants/app_colors.dart';
import '../constants/app_radius.dart';
import '../services/auth_service.dart';
import 'app_dialog.dart';
import 'form_kit.dart';

/// "Forgot Password?" from the login screen. Mirrors the web flow: the server
/// e-mails a reset link, the new password is chosen on the web reset page,
/// then the user signs in here again.
Future<void> showForgotPasswordDialog(BuildContext context, {String initialLogin = ''}) {
  return showAppDialog<void>(
    context: context,
    builder: (_) => _ForgotPasswordDialog(initialLogin: initialLogin),
  );
}

class _ForgotPasswordDialog extends StatefulWidget {
  final String initialLogin;
  const _ForgotPasswordDialog({required this.initialLogin});

  @override
  State<_ForgotPasswordDialog> createState() => _ForgotPasswordDialogState();
}

class _ForgotPasswordDialogState extends State<_ForgotPasswordDialog> {
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
    return AppDialog(
      size: AppDialogSize.small,
      header: AppDialogHeader(
        icon: sent ? Icons.mark_email_read_outlined : Icons.lock_reset_rounded,
        title: sent ? 'Check your email' : 'Forgot password',
        subtitle: sent ? null : 'We will email you a reset link',
        onClose: _sending ? null : () => Navigator.of(context).maybePop(),
      ),
      body: sent ? _sentBody() : _formBody(),
      actions: sent
          ? [AppPrimaryButton(label: 'Back to sign in', onPressed: () => Navigator.of(context).pop())]
          : [
              AppSecondaryButton(onPressed: _sending ? null : () => Navigator.of(context).maybePop()),
              AppPrimaryButton(label: 'Send reset link', icon: Icons.send_rounded, loading: _sending, onPressed: _send),
            ],
    );
  }

  Widget _formBody() {
    return Form(
      key: _formKey,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            'Enter the email or phone number you sign in with.',
            style: GoogleFonts.poppins(fontSize: 13, fontWeight: FontWeight.w500, color: AppColors.textSecondary),
          ),
          const SizedBox(height: 14),
          if (_error != null) ...[
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
              decoration: BoxDecoration(
                color: const Color(0xFFFFDAD6),
                borderRadius: BorderRadius.circular(AppRadius.md),
              ),
              child: Row(
                children: [
                  const Icon(Icons.error_outline_rounded, color: Color(0xFFBA1A1A), size: 18),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      _error!,
                      style: GoogleFonts.poppins(fontSize: 12, fontWeight: FontWeight.w600, color: const Color(0xFFBA1A1A)),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
          ],
          TextFormField(
            controller: _loginCtrl,
            autofocus: true,
            enabled: !_sending,
            keyboardType: TextInputType.emailAddress,
            textInputAction: TextInputAction.done,
            onFieldSubmitted: (_) => _send(),
            validator: (v) => (v == null || v.trim().isEmpty) ? 'Please enter your email or phone' : null,
            decoration: InputDecoration(
              labelText: 'Email or phone',
              prefixIcon: const Icon(Icons.mail_outline_rounded),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _sentBody() {
    return Column(
      mainAxisSize: MainAxisSize.min,
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'A password reset link was sent to $_sentTo.',
          style: GoogleFonts.poppins(fontSize: 14, fontWeight: FontWeight.w700, color: AppColors.primary),
        ),
        const SizedBox(height: 10),
        Text(
          'Open the email and tap the link. It opens in your browser, where you set a new password. '
          'Then come back here and sign in with it. The link is valid for 60 minutes. '
          'If you do not see the email, check your spam folder.',
          style: GoogleFonts.poppins(fontSize: 13, fontWeight: FontWeight.w500, height: 1.5, color: AppColors.textSecondary),
        ),
      ],
    );
  }
}
