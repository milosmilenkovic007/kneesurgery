# Hello Elementor Child Theme

## Asset workflow

Source files stay in these folders:

- `style.css`
- `assets/css/**`
- `assets/js/**`

Minified build output is written to:

- `dist/style.css`
- `dist/assets/css/**`
- `dist/assets/js/**`
- `dist/asset-manifest.json`

These `dist/` files are tracked in Git because this theme is deployed to the server directly from the repository.

### Commands

Install build dependencies:

```bash
npm install
```

Run a one-time minified build:

```bash
npm run build
```

Watch source files while developing:

```bash
npm run watch:assets
```

Remove generated files:

```bash
npm run clean:assets
```

### How enqueue works

Theme PHP uses `hj_get_theme_asset()` from `functions.php`.

- If a built file exists in `dist/`, WordPress enqueues that file.
- If no built file exists, WordPress falls back to the source file inside the theme.
- If `dist/asset-manifest.json` exists, its content hash is used for the asset version.
- If the manifest is missing, the fallback version is `filemtime()` for built files or the theme version for source files.

### Recommended workflow

1. Edit source files in `assets/` or `style.css`.
2. Keep `npm run watch:assets` running during development.
3. Run `npm run build` before pushing deployment changes.
4. Commit the updated `dist/` files together with the source changes.
5. Let the theme serve the generated files from `dist/` automatically.