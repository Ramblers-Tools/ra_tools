# Contributing

## Workflow

```
feature branch → PR to beta → PR to main (release)
```

### Making a change

1. Cut a branch from `beta`:
   ```bash
   git fetch origin
   git checkout -b my-feature origin/beta
   ```
2. Commit your changes with clear messages.
3. Push and open a **PR targeting `beta`**:
   ```bash
   git push -u origin my-feature
   ```
4. Once the PR is merged and the beta pre-release workflow passes, open a second **PR from `beta` → `main`** to ship the release.

### Releasing

Merging the `beta` → `main` PR does **not** trigger a release by itself. After it's merged, tag the merged commit and push the tag — that's what triggers `.github/workflows/release.yml`:

```bash
git fetch origin
git tag vX.Y.Z origin/main
git push origin vX.Y.Z
```

The release workflow then:

1. Reads the version from `pkg_ra_tools/pkg_ra_tools.xml`.
2. Builds and zips all extensions.
3. Creates a GitHub Release with the package zip attached.
4. Computes the `sha256` of the zip.
5. Updates `updates/pkg_ra_tools.xml` with the new version, download URL, and sha256.
6. Opens a PR titled `Update update manifest for vX.Y.Z` against `main` with that change.

Review and merge that PR so Joomla's auto-update system picks up the new version.

### Commit messages

Use the imperative mood: *Add*, *Fix*, *Remove*, not *Added*, *Fixed*, *Removed*.
One subject line (≤72 chars); blank line; optional body for the *why*.
