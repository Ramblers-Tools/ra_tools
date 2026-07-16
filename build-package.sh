#!/usr/bin/env bash
set -euo pipefail

component="com_ra_tools"
manifest="ra_tools.xml"
version="$(sed -n 's:.*<version>\(.*\)</version>.*:\1:p' "$manifest" | head -1)"
package="${component}-${version}.zip"
tag="v${version}"
repo="Ramblers-Tools/com_ra_tools"

rm -rf dist
mkdir -p dist

zip -r "dist/${package}" "$manifest" admin forms languages src tmpl \
	-x '.DS_Store' \
	-x '*/.DS_Store' \
	-x '._*' \
	-x '*/._*' \
	-x '*.old' \
	-x '*.old2'

echo "Created dist/${package}"

# Update updates/com_ra_tools.xml with local sha256 (pre-upload estimate)
local_sha256="$(shasum -a 256 "dist/${package}" | awk '{print $1}')"
sed -i.bak "s|<version>.*</version>|<version>${version}</version>|" updates/com_ra_tools.xml
sed -i.bak "s|releases/tag/v[^<]*|releases/tag/${tag}|" updates/com_ra_tools.xml
sed -i.bak "s|releases/download/v[^/]*/[^<]*|releases/download/${tag}/${package}|" updates/com_ra_tools.xml
sed -i.bak "s|<sha256>.*</sha256>|<sha256>${local_sha256}</sha256>|" updates/com_ra_tools.xml
rm -f updates/com_ra_tools.xml.bak
echo "Updated updates/com_ra_tools.xml (local sha256: ${local_sha256})"

# If --release flag passed, create GitHub release and fix sha256 from the actual uploaded file
if [[ "${1:-}" == "--release" ]]; then
	echo "Creating GitHub release ${tag}..."
	gh release create "${tag}" "dist/${package}" \
		--repo "${repo}" \
		--title "${tag}" \
		--target main \
		--generate-notes

	echo "Fetching sha256 from uploaded release asset..."
	download_url="https://github.com/${repo}/releases/download/${tag}/${package}"
	released_sha256="$(curl -sL "${download_url}" | shasum -a 256 | awk '{print $1}')"
	sed -i.bak "s|<sha256>.*</sha256>|<sha256>${released_sha256}</sha256>|" updates/com_ra_tools.xml
	rm -f updates/com_ra_tools.xml.bak
	echo "Corrected sha256 to: ${released_sha256}"
	echo "Commit and push updates/com_ra_tools.xml to apply the fix."
fi
