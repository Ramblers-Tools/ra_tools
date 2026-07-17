# RA Tools

A Joomla 5/6 extension package for Ramblers local groups and areas. Provides a menu-driven interface to the RamblersWebs Library routines and is a prerequisite for `ra_mailman` and `ra-events`.

## What's included

| Extension | Type | Purpose |
|-----------|------|---------|
| `com_ra_tools` | Component | Core component — admin and site views |
| `mod_ra_tools` | Module | Front-end module |
| `plg_webservices_ra_tools` | Plugin (webservices) | REST API endpoints |
| `plg_finder_ra_toolswalks` | Plugin (finder) | Smart Search indexer for walks |

## Installing

1. Download the latest `pkg_ra_tools-<version>.zip` from the [Releases](https://github.com/Ramblers-Tools/ra_tools/releases) page.
2. In Joomla: **Extensions → Install → Upload Package File** — select the zip.
3. Enable the plugins under **Extensions → Plugins** if not auto-enabled.

### Updating

Joomla's built-in update system will detect new releases automatically (the update server URL is baked into the package manifest). Go to **System → Update → Extensions** and update from there.

## Branch model

| Branch | Purpose |
|--------|---------|
| `main` | Stable, released code. Protected — merge via PR only. Every push here triggers the release workflow. |
| `beta` | Integration branch. New features land here first; a passing beta build produces a pre-release. |
| feature branches | Short-lived branches cut from `beta` for individual changes. |

Releases are tagged automatically by the GitHub Actions release workflow when a PR is merged to `main`.

## Development setup

```bash
git clone https://github.com/Ramblers-Tools/ra_tools.git
cd ra_tools
# Use setup-joomla-symlinks.sh to symlink extension folders into a local Joomla install
bash setup-joomla-symlinks.sh /path/to/joomla
```

See [CONTRIBUTING.md](CONTRIBUTING.md) for the full contribution workflow.
