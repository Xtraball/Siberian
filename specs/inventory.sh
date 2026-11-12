#!/bin/sh
# Regenerates specs/manifest.tsv: one row per controller action under siberian/app/sae/modules/*/controllers.
#   module  url  context  verb  class
# context: which session the endpoint expects (from its base controller):
#   backoffice  administrator of /backoffice          editor  an account of the editor (/admin, /application, features)
#   mobile      the mobile app (public, app context)   api     the public API   installer  the web installer   public  anything else
# verb: POST for actions that change something (by name), GET otherwise. Rows can be corrected by hand in
# specs/overrides.tsv, which is applied on top (the manifest itself is regenerated, never edited).
cd "$(dirname "$0")/../siberian/app/sae/modules" || exit 1
for f in $(find . -path '*/controllers/*' -name '*Controller.php' | sort); do
  m=$(echo "$f" | cut -d/ -f2)
  rel=${f#./$m/controllers/}
  c=$(echo "${rel%Controller.php}" | tr '/' '_' | tr 'A-Z' 'a-z')
  base=$(grep -m1 -o 'extends [A-Za-z_]*' "$f" | cut -d' ' -f2)
  case "$base" in
    Backoffice_Controller_Default|System_Controller_Backoffice_Default) ctx=backoffice ;;
    Admin_Controller_Default|Application_Controller_Default|Application_Controller_Default_Ajax) ctx=editor ;;
    Application_Controller_Mobile_Default|Mcommerce_Controller_Mobile_Default|AbstractMobilePaymentController|MobileController|Front_Controller_App_Default) ctx=mobile ;;
    Api_Controller_Default) ctx=api ;;
    Installer_Controller_Installation_Default) ctx=installer ;;
    *) ctx=public ;;
  esac
  # Zend routes a camelCase action (checkModuleLicense) as check-module-license; a lower-cased name does not route
  for a in $(grep -o 'public function [a-zA-Z0-9_]*Action' "$f" | sed 's/public function //; s/Action$//' | sed 's/\([a-z0-9]\)\([A-Z]\)/\1-\2/g' | tr 'A-Z' 'a-z'); do
    case "$a" in
      *save*|*delete*|*remove*|*create*|*update*|*post|*post[a-z]*|*add*|*set*|*upload*|*install*|*edit*|*duplicate*|*sort*|*order*|*send*|*login*|*logout*|*signup*|*clear*|*reset*|*copy*|*import*|*export*|*toggle*|*validate*|*cancel*|*pay*|*submit*|*like*|*follow*) verb=POST ;;
      *) verb=GET ;;
    esac
    printf '%s\t/%s/%s/%s\t%s\t%s\t%s\n' "$m" "$(echo "$m" | tr 'A-Z' 'a-z')" "$c" "$a" "$ctx" "$verb" "$base"
  done
done
