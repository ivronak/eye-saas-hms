class AppConfig {
  AppConfig._();

  // ── Environment toggle ───────────────────────────────────────────────────
  // Set to true before building the release APK / Play Store upload.
  static const bool isProduction = true;

  // ── URLs ─────────────────────────────────────────────────────────────────
  // Cleartext HTTP is disabled in the Android manifest, so every URL must be
  // https. To test against a local server, temporarily point _devUrl at it,
  // set isProduction = false, and re-enable usesCleartextTraffic in the
  // manifest (e.g. `http://localhost:8000/api/v1` + `adb reverse tcp:8000
  // tcp:8000`). Revert all three before building a release.
  static const String _devUrl  = 'https://eyenosis.com/api/v1';
  static const String _prodUrl = 'https://eyenosis.com/api/v1';

  static const String apiBaseUrl = isProduction ? _prodUrl : _devUrl;

  // Platform Super Admin — no slug, platform-wide
  static const String platformApiUrl = '$apiBaseUrl/super';

  // ── Hospital session state ────────────────────────────────────────────────
  // Slug is auto-discovered at login via GET /api/v1/find-hospital
  // and restored from SharedPreferences on app restart.
  static String slug = '';
  static String hospitalName = 'Eyenosis';

  static String get hospitalApiUrl => '$apiBaseUrl/$slug';

  static const Duration requestTimeout = Duration(seconds: 30);
}
