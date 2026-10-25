#!/bin/sh
# Test accounts and data, created through Siberian's own API and backoffice endpoints, never SQL.
#
#   sh specs/fixtures.sh up      create (or reuse) an API user, an editor account and an application; writes specs/.fixtures.env
#   sh specs/fixtures.sh down    delete the editor account and the API user (a Single App Edition cannot delete its application)
#   sh specs/fixtures.sh show
#
# Sourced by contracts.sh through `. specs/.fixtures.env` (SPEC_EDITOR_EMAIL, SPEC_EDITOR_PASSWORD, SPEC_EDITOR_ID, SPEC_APP_ID).
. "$(dirname "$0")/lib.sh"
ENVFILE="$(dirname "$0")/.fixtures.env"
API_USER=spec_api
API_PASS=spec-api-pass
ED_EMAIL=spec-editor@example.com
ED_PASS='Sp3c-Editor-Pass!'

fx_up() {
  login
  # API user able to create accounts and applications (the API is how a test account is meant to be created)
  request POST /api/backoffice_user_edit/save "{\"username\":\"$API_USER\",\"password\":\"$API_PASS\",\"confirm_password\":\"$API_PASS\",\"acl\":{\"user\":{\"create\":true,\"exist\":true,\"authenticate\":true,\"update\":true},\"application\":{\"create\":true,\"list\":true}}}"
  # the editor account
  r=$(curl -s -m 20 -u "$API_USER:$API_PASS" --data-urlencode "email=$ED_EMAIL" --data-urlencode "password=$ED_PASS" "$SPEC_BASE/admin/api_account/create")
  ED_ID=$(printf '%s' "$r" | jq -r '.user_id // empty')
  if [ -z "$ED_ID" ]; then
    request GET /admin/backoffice_list/findall
    ED_ID=$(printf '%s' "$BODY" | jq -r --arg e "$ED_EMAIL" '[.. | objects | select(.email? == $e) | .id][0] // empty')
  fi
  [ -n "$ED_ID" ] || { echo "cannot create or find the editor account: $r"; exit 2; }
  # the application of that account (reused when it exists)
  request GET /application/backoffice_list/findall
  APP_ID=$(printf '%s' "$BODY" | jq -r '[.. | objects | select(.id? != null and .name? == "SpecApp") | .id][0] // empty')
  if [ -z "$APP_ID" ]; then
    r=$(curl -s -m 20 -u "$API_USER:$API_PASS" -d name=SpecApp -d "user_id=$ED_ID" -d bundle_id=com.spec.app "$SPEC_BASE/application/api/create")
    APP_ID=$(printf '%s' "$r" | jq -r '.app_id // empty')
  fi
  [ -n "$APP_ID" ] || { echo "cannot create or find the application: $r"; exit 2; }
  printf 'SPEC_EDITOR_EMAIL=%s\nSPEC_EDITOR_PASSWORD=%s\nSPEC_EDITOR_ID=%s\nSPEC_APP_ID=%s\n' "$ED_EMAIL" "$ED_PASS" "$ED_ID" "$APP_ID" > "$ENVFILE"
  echo "fixtures ready: editor $ED_EMAIL (id $ED_ID), application $APP_ID"
}

fx_down() {
  login
  [ -f "$ENVFILE" ] && . "$ENVFILE"
  [ -n "${SPEC_EDITOR_ID:-}" ] && { request POST /admin/backoffice_list/delete "{\"admin_id\":$SPEC_EDITOR_ID}"; echo "editor: $CODE"; }
  request GET /api/backoffice_user_list/findall
  uid=$(printf '%s' "$BODY" | jq -r --arg u "$API_USER" '[.. | objects | select(.username? == $u) | .id][0] // empty')
  [ -n "$uid" ] && { request POST /api/backoffice_user_list/delete "{\"user_id\":$uid}"; echo "api user: $CODE"; }
  rm -f "$ENVFILE"
}

case "${1:-}" in
  up) fx_up ;;
  down) fx_down ;;
  show) cat "$ENVFILE" ;;
  *) echo "usage: fixtures.sh up|down|show"; exit 1 ;;
esac
