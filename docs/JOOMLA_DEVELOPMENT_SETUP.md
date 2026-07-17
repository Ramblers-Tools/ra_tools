# Joomla Development Setup with Symlinked Git Repositories


## Overview

This document describes the architecture for developing Joomla components using symlinked git repositories in a local MAMP environment. This approach allows version control of component source code while maintaining Joomla's ability to discover and load extensions.

## Architecture

### Problem Statement

Joomla's extension discovery system requires manifest files (XML) to be present in the component directory where Joomla looks for them. However, our development workflow uses:

- **Source repository**: Organized in git with separate `site/`, `administrator/`, and `media/` subdirectories
- **Manifest file**: Stored at the repository root (e.g., `ra_tools.xml`)
- **MAMP installation**: The local Joomla installation expects components in `/Applications/MAMP/htdocs/components/` and `/Applications/MAMP/htdocs/administrator/components/`

This creates a mismatch: Joomla can't find manifest files when the component points to a git subdirectory.

### Solution: Double-Symlink Strategy

We use a two-level symlink approach to maintain a single source of truth while satisfying Joomla's directory expectations:

```
MAMP Directory
  └─ /Applications/MAMP/htdocs/components/com_ra_tools
       └─ symlink → /Users/charlie/git/ra-tools/com_ra_tools/site
            └─ ra_tools.xml
                 └─ symlink → ../ra_tools.xml (root manifest)
                      └─ actual file
```

**Level 1 Symlink** (MAMP → Git)
- `/Applications/MAMP/htdocs/components/com_ra_tools` → `/Users/charlie/git/ra-tools/com_ra_tools/site`
- `/Applications/MAMP/htdocs/administrator/components/com_ra_tools` → `/Users/charlie/git/ra-tools/com_ra_tools/administrator`

**Level 2 Symlink** (Git subdirectory → Root)
- `/Users/charlie/git/ra-tools/com_ra_tools/site/ra_tools.xml` → `../ra_tools.xml`
- `/Users/charlie/git/ra-tools/com_ra_tools/administrator/ra_tools.xml` → `../ra_tools.xml`

### Benefits

1. **Single Source of Truth**: Only one manifest file exists (`ra_tools.xml` at repo root)
2. **Automatic Synchronization**: Changes to the root manifest propagate to both site and administrator copies
3. **Git-Native**: Symlinks are committed to git, so the architecture is consistent across all development machines
4. **No Manual Duplication**: Eliminates the error-prone task of maintaining duplicate manifest files

## Setup Instructions

### Automated Setup (Recommended)

Run the provided setup script:

```bash
cd /Users/charlie/git/ra-tools
./setup-joomla-symlinks.sh
```

The script will:
1. Create Level 1 symlinks (MAMP → Git source)
2. Create Level 2 symlinks (manifest files at git root)
3. Verify all symlinks are correctly resolved
4. Clear Joomla's autoloader cache to force regeneration

### Manual Setup

If you prefer to set up manually:

**1. Create Level 1 Symlinks (Git source → MAMP)**

```bash
# Site component
ln -s /Users/charlie/git/ra-tools/com_ra_tools/site \
      /Applications/MAMP/htdocs/components/com_ra_tools

# Administrator component
ln -s /Users/charlie/git/ra-tools/com_ra_tools/administrator \
      /Applications/MAMP/htdocs/administrator/components/com_ra_tools
```

**2. Create Level 2 Symlinks (manifest files in git subdirectories)**

```bash
# Site manifest symlink
cd /Users/charlie/git/ra-tools/com_ra_tools/site
ln -s ../ra_tools.xml ra_tools.xml

# Administrator manifest symlink
cd /Users/charlie/git/ra-tools/com_ra_tools/administrator
ln -s ../ra_tools.xml ra_tools.xml
```

**3. Clear Joomla's Autoloader Cache**

```bash
rm -f /Applications/MAMP/htdocs/administrator/cache/autoload_psr4.php
```

**4. Enable the Component in Joomla**

- Log into Joomla admin
- Go to **Extensions > Manage**
- Find `com_ra_tools` and click the radio button to enable it
- The component will now be discoverable and functional

## Editing Manifest Files

When preparing a new release or updating the manifest:

**Edit only the root manifest file:**
```
/Users/charlie/git/ra-tools/com_ra_tools/ra_tools.xml
```

**Do NOT edit:**
- `/Users/charlie/git/ra-tools/com_ra_tools/site/ra_tools.xml` (symlink)
- `/Users/charlie/git/ra-tools/com_ra_tools/administrator/ra_tools.xml` (symlink)

The symlinked copies will automatically reflect any changes you make to the root file.

## How Joomla Discovers Extensions

When Joomla starts, it:

1. Scans `/Applications/MAMP/htdocs/components/` for subdirectories (finds `com_ra_tools`)
2. Looks for manifest files in each component directory
3. Follows the Level 1 symlink to `/Users/charlie/git/ra-tools/com_ra_tools/site/`
4. Finds `ra_tools.xml` and follows the Level 2 symlink to the root manifest
5. Reads the manifest to extract namespace information
6. Caches namespace mappings in `/administrator/cache/autoload_psr4.php`
7. Automatically loads classes from the defined namespaces at runtime

## Git Workflow

All symlinks are committed to the repository:

```bash
git add com_ra_tools/site/ra_tools.xml
git add com_ra_tools/administrator/ra_tools.xml
git commit -m "Add manifest symlinks for Joomla discovery"
git push origin main
```

When cloning or pulling on another development machine:
- The symlinks will be restored automatically by git
- Run `./setup-joomla-symlinks.sh` to create the MAMP-side symlinks (Level 1)
- Clear the Joomla cache as described above

## Troubleshooting

### "Class not found" errors

**Cause**: Manifest files not discoverable or autoloader cache stale

**Solution**:
```bash
rm -f /Applications/MAMP/htdocs/administrator/cache/autoload_psr4.php
```
Then reload the Joomla site. The cache will regenerate with correct namespace mappings.

### Component not appearing in Joomla Extensions

**Causes**:
1. Manifest file not found by Joomla
2. Component disabled in database
3. Incorrect namespace declaration in manifest

**Solutions**:
1. Verify Level 1 symlink: `ls -la /Applications/MAMP/htdocs/components/com_ra_tools`
2. Verify Level 2 symlink: `ls -la /Applications/MAMP/htdocs/components/com_ra_tools/ra_tools.xml`
3. Check Joomla admin: Extensions > Manage (enable if disabled)
4. Check manifest namespace: `<namespace path="src">Ramblers\Component\Ra_tools</namespace>`

### Symlink appears broken in git

Symlinks should appear as regular files in `git status`:

```bash
$ git status
	new file:   com_ra_tools/site/ra_tools.xml
	new file:   com_ra_tools/administrator/ra_tools.xml
```

On other development machines, after cloning/pulling, git automatically restores these as symlinks.

## Dependency Note

**com_ra_tools is the foundational component.** All other Ramblers components (com_ra_events, com_ra_mailman, com_ra_walks) follow the identical symlink architecture and depend on the patterns established here.

For setup instructions on subsidiary components, refer to their respective repositories' JOOMLA_DEVELOPMENT_SETUP.md files, which will reference this document.
