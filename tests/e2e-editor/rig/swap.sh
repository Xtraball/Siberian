#!/bin/bash
# usage: swap.sh trunk|pr  — puts the three files of PR #1006 (or their trunk version) into the rig's Siberian tree.
set -e
R=/srv/data/sib-pr/rel519
W=/srv/data/pw-editor/siberian
cd $R
for f in siberian/app/sae/design/desktop/flat/css/style.css \
         siberian/app/sae/modules/Application/resources/design/desktop/flat/template/application/customization/index.phtml \
         siberian/app/sae/modules/Application/resources/design/desktop/flat/template/application/customization/index/overview/publication.phtml; do
  dest=$W/${f#siberian/}
  if [ "$1" = trunk ]; then git show origin/trunk:$f > $dest; else git show feat/preview-responsive:$f > $dest; fi
done
# the compiled design cache must follow the CSS
docker compose -f /srv/data/pw-editor/rig/docker-compose.yml exec -T siberian php cli cache:design sae >/dev/null 2>&1 || true
docker compose -f /srv/data/pw-editor/rig/docker-compose.yml exec -T siberian php cli cache:clear >/dev/null 2>&1 || true
echo "swapped to $1: $(grep -c overview-toggle $W/app/sae/modules/Application/resources/design/desktop/flat/template/application/customization/index.phtml) toggle refs"
