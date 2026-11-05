/// The one dialog frame every tablet dialog should use: a fixed header, a
/// body that is the only part that scrolls, and a footer whose actions stay
/// visible no matter how long the body gets.
///
/// Every dialog is opened through [showAppDialog] (or [showPaneDialog]), which
/// gives all of them the same lightweight "spring pop" open / quick snap
/// close, a dark scrim, tap-outside / Esc / back to dismiss, and a guard that
/// asks before throwing away edits the user actually typed.
library;

import 'package:flutter/material.dart';
import '../constants/app_colors.dart';
import '../constants/app_radius.dart';
import 'form_kit.dart';

/// The only three dialog widths on tablet.
enum AppDialogSize {
  /// Confirms and 1–3 field master forms.
  small(440),

  /// Detail views and 4–12 field forms.
  medium(640),

  /// Patient, check-in, counselling, surgery and user forms.
  large(920);

  const AppDialogSize(this.width);
  final double width;
}

// ── Open / close motion ────────────────────────────────────────────────────
//
// Kept deliberately cheap: one scale + a small rise + a fade driven by a
// single animation, the dialog subtree wrapped in a RepaintBoundary, and a
// flat dark scrim (no backdrop blur — blur re-samples the whole screen every
// frame and is what makes dialogs janky on mid-range tablets).

const _springOpen = Cubic(0.34, 1.56, 0.64, 1);

/// Opens [builder] as an app dialog. Drop-in for `showDialog` — same
/// `context` / `builder` / `barrierDismissible` — but with the shared motion,
/// scrim and dismiss guard. Tapping outside, pressing Esc/back or calling
/// `Navigator.maybePop` closes it; if the user has typed into a field since
/// they started interacting, a "Discard changes?" confirm comes first.
/// Pass `barrierDismissible: false` only for dialogs that must be answered
/// (stale-record notice, blocking progress).
Future<T?> showAppDialog<T>({
  required BuildContext context,
  required WidgetBuilder builder,
  bool barrierDismissible = true,
  bool useRootNavigator = true,
  Color? barrierColor,
}) {
  return showGeneralDialog<T>(
    context: context,
    useRootNavigator: useRootNavigator,
    barrierDismissible: barrierDismissible,
    barrierLabel: MaterialLocalizations.of(context).modalBarrierDismissLabel,
    barrierColor: barrierColor ?? const Color(0x730F172A),
    transitionDuration: const Duration(milliseconds: 360),
    transitionBuilder: (ctx, animation, secondary, child) {
      final forward = CurvedAnimation(parent: animation, curve: _springOpen, reverseCurve: Curves.easeInCubic);
      final fade = CurvedAnimation(parent: animation, curve: const Interval(0, 0.6, curve: Curves.easeOut), reverseCurve: Curves.easeIn);
      return FadeTransition(
        opacity: fade,
        child: AnimatedBuilder(
          animation: forward,
          child: child,
          builder: (_, c) {
            // Overshoot (>1) is intentional on open; clamp the rise so the
            // card never travels past its resting position by much.
            final t = forward.value;
            return Transform.translate(
              offset: Offset(0, (1 - t) * 22),
              child: Transform.scale(scale: 0.9 + 0.1 * t, child: c),
            );
          },
        ),
      );
    },
    pageBuilder: (ctx, _, _) => _DismissGuard(
      enabled: barrierDismissible,
      child: RepaintBoundary(child: Builder(builder: builder)),
    ),
  );
}

/// Closes the dialog on back / Esc / outside tap, asking first when the user
/// has edited a text field. Dirty = a pointer went down inside the dialog AND
/// some text field's text now differs from what it held at that first touch —
/// so pre-filled edit forms and async-loaded values never count as edits.
class _DismissGuard extends StatefulWidget {
  final bool enabled;
  final Widget child;
  const _DismissGuard({required this.enabled, required this.child});

  @override
  State<_DismissGuard> createState() => _DismissGuardState();
}

class _DismissGuardState extends State<_DismissGuard> {
  final Map<EditableTextState, String> _snapshot = {};
  bool _interacted = false;
  bool _confirming = false;

  void _forEachEditable(void Function(EditableTextState) visit) {
    void walk(Element e) {
      if (e is StatefulElement && e.state is EditableTextState) visit(e.state as EditableTextState);
      e.visitChildren(walk);
    }

    (context as Element).visitChildren(walk);
  }

  void _onPointerDown(PointerDownEvent _) {
    if (_interacted) return;
    _interacted = true;
    _forEachEditable((s) => _snapshot[s] = s.widget.controller.text);
  }

  bool get _dirty {
    if (!_interacted) return false;
    var dirty = false;
    _forEachEditable((s) {
      if (s.widget.controller.text != (_snapshot[s] ?? '')) dirty = true;
    });
    return dirty;
  }

  Future<void> _onPop(bool didPop, Object? _) async {
    if (didPop || _confirming) return;
    final nav = Navigator.of(context);
    if (!_dirty) {
      nav.pop();
      return;
    }
    _confirming = true;
    final discard = await showAppDialog<bool>(
      context: context,
      builder: (ctx) => AppDialog(
        size: AppDialogSize.small,
        header: AppDialogHeader(
          icon: Icons.edit_off_rounded,
          title: 'Discard changes?',
          colors: const [AppColors.orange, AppColors.orange],
          onClose: () => Navigator.pop(ctx, false),
        ),
        body: const Text('You have unsaved changes. If you close now they will be lost.', style: TextStyle(fontSize: 14, height: 1.45, color: AppColors.darkNavy)),
        actions: [
          OutlinedButton(
            onPressed: () => Navigator.pop(ctx, false),
            style: OutlinedButton.styleFrom(
              foregroundColor: AppColors.textSecondary,
              padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 20),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
              side: BorderSide(color: AppColors.primaryA22),
            ),
            child: const Text('Keep editing'),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: AppColors.red,
              foregroundColor: Colors.white,
              elevation: 0,
              padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 20),
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.md)),
            ),
            child: const Text('Discard', style: TextStyle(fontWeight: FontWeight.w700)),
          ),
        ],
      ),
    );
    _confirming = false;
    if (discard == true && mounted) nav.pop();
  }

  @override
  Widget build(BuildContext context) {
    if (!widget.enabled) return widget.child;
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: _onPop,
      child: Listener(behavior: HitTestBehavior.translucent, onPointerDown: _onPointerDown, child: widget.child),
    );
  }
}

/// Hosts a full form pane that draws its own header and actions (Patient
/// form, OT appointment, user, counselling, ward, ...), on the same white
/// untinted surface as [AppDialog]. [wide] is for the multi-section panes.
Future<T?> showPaneDialog<T>(
  BuildContext context, {
  required Widget Function(BuildContext dCtx) builder,
  bool wide = false,
  bool barrierDismissible = true,
}) {
  return showAppDialog<T>(
    context: context,
    barrierDismissible: barrierDismissible,
    builder: (dCtx) => Dialog(
      backgroundColor: Colors.white,
      surfaceTintColor: Colors.transparent,
      clipBehavior: Clip.antiAlias,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.xl)),
      child: ConstrainedBox(
        constraints: wide ? const BoxConstraints(maxWidth: 920, maxHeight: 800) : const BoxConstraints(maxWidth: 760, maxHeight: 680),
        child: Padding(padding: const EdgeInsets.all(20), child: builder(dCtx)),
      ),
    ),
  );
}

class AppDialog extends StatelessWidget {
  /// Usually an [AppDialogHeader].
  final Widget header;
  final Widget body;

  /// Laid out right-aligned in the footer: Cancel/Close first, the primary
  /// action last.
  final List<Widget> actions;
  final AppDialogSize size;
  final EdgeInsetsGeometry bodyPadding;

  const AppDialog({
    super.key,
    required this.header,
    required this.body,
    this.actions = const [],
    this.size = AppDialogSize.medium,
    this.bodyPadding = const EdgeInsets.fromLTRB(24, 20, 24, 20),
  });

  @override
  Widget build(BuildContext context) {
    final screenHeight = MediaQuery.sizeOf(context).height;
    return Dialog(
      backgroundColor: Colors.white,
      surfaceTintColor: Colors.transparent,
      insetPadding: const EdgeInsets.all(24),
      clipBehavior: Clip.antiAlias,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppRadius.xl)),
      child: ConstrainedBox(
        constraints: BoxConstraints(maxWidth: size.width, maxHeight: screenHeight * 0.9),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            header,
            Flexible(child: SingleChildScrollView(padding: bodyPadding, child: body)),
            if (actions.isNotEmpty)
              Container(
                padding: const EdgeInsets.fromLTRB(24, 12, 24, 16),
                decoration: BoxDecoration(color: AppColors.primaryA04, border: Border(top: BorderSide(color: AppColors.primaryA08))),
                child: Wrap(
                  alignment: WrapAlignment.end,
                  spacing: 10,
                  runSpacing: 8,
                  children: actions,
                ),
              ),
          ],
        ),
      ),
    );
  }
}
