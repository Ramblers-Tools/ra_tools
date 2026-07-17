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

Merging a PR into `main` triggers `.github/workflows/release.yml`, which:

1. Reads the version from `pkg_ra_tools/pkg_ra_tools.xml`.
2. Builds and zips all extensions.
3. Creates a GitHub Release with the package zip attached.
4. Prints the `sha256` of the zip in the release notes.

After the release workflow completes:

1. Copy the `sha256` from the release notes.
2. Update `updates/pkg_ra_tools.xml` with the new version, download URL, and sha256.
3. Open a PR to `main` with just that change so Joomla's auto-update system picks it up.

### Commit messages

Use the imperative mood: *Add*, *Fix*, *Remove*, not *Added*, *Fixed*, *Removed*.
One subject line (≤72 chars); blank line; optional body for the *why*.
