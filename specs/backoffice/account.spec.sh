#!/bin/sh
# Backoffice account controllers: account_view (load, find, save), account_list (load, findall, delete),
# account_login (post, forgottenpassword, logout). One valid and one invalid case per endpoint, plus the
# empty-body cases that used to answer 404 (no response at all from the action).
. "$(dirname "$0")/../lib.sh"

login
STAMP=$(date +%s)
NEW="spec-$STAMP@example.com"

spec_name "account_view/load"
request GET /backoffice/account_view/load
expect_status "valid" 200
expect_json "valid" .icon fa-users
# without a session the backoffice answers with its login page, never with data
SPEC_JAR_SAVE=$SPEC_JAR; SPEC_JAR=$(mktemp /tmp/spec.XXXXXX)
request GET /backoffice/account_list/findall
expect_eq "invalid (not logged in): no user data" "" "$(json .users)"
rm -f "$SPEC_JAR"; SPEC_JAR=$SPEC_JAR_SAVE

spec_name "account_view/find"
request GET "/backoffice/account_view/find?user_id=1"
expect_status "valid (existing user)" 200
expect_json "valid" .user.email "$SPEC_EMAIL"
request GET "/backoffice/account_view/find?user_id=99999999"
expect_status "invalid (unknown user is the create form)" 200
expect_json "invalid" '.user.id' ""
expect_json "invalid" .section_title "Create a user"

spec_name "account_view/save"
request POST /backoffice/account_view/save "{\"email\":\"$NEW\",\"password\":\"Sp3c-pass-$STAMP\",\"confirm_password\":\"Sp3c-pass-$STAMP\"}"
expect_status "valid (create)" 200
expect_json "valid (create)" .success 1
request GET /backoffice/account_list/findall
NEW_ID=$(printf '%s' "$BODY" | jq -r --arg e "$NEW" '.users[] | select(.email==$e) | .id')
[ -n "$NEW_ID" ] && pass "created user is listed" || fail "created user is listed" "not found"
request POST /backoffice/account_view/save "{\"id\":$NEW_ID,\"email\":\"$NEW\"}"
expect_status "valid (edit without changing the password)" 200
request POST /backoffice/account_view/save '{"email":"not-an-email"}'
expect_status "invalid (bad email)" 400
expect_json "invalid (bad email)" .error 1
request POST /backoffice/account_view/save "{\"email\":\"$NEW\",\"password\":\"a\",\"confirm_password\":\"b\"}"
expect_status "invalid (passwords differ)" 400
request POST /backoffice/account_view/save '{"password":"x","confirm_password":"x"}'
expect_status "invalid (no email)" 400
request POST /backoffice/account_view/save '{}'
expect_status "invalid (empty object, was 404)" 400
expect_json "invalid (empty object)" .error 1
request POST /backoffice/account_view/save
expect_status "invalid (no body, was 404)" 400
request POST /backoffice/account_view/save '{not json'
expect_status "invalid (broken JSON)" 400

spec_name "account_list/load"
request GET /backoffice/account_list/load
expect_status "valid" 200
expect_json "valid" .icon fa-users

spec_name "account_list/findall"
request GET /backoffice/account_list/findall
expect_status "valid" 200
expect_eq "valid: the admin is listed" 1 "$(printf '%s' "$BODY" | jq --arg e "$SPEC_EMAIL" '[.users[] | select(.email==$e)] | length')"

spec_name "account_list/delete"
request POST /backoffice/account_list/delete '{"user_id":99999999}'
expect_status "invalid (unknown user)" 400
expect_json "invalid (unknown user)" .error 1
request POST /backoffice/account_list/delete '{}'
expect_status "invalid (empty object, was 404)" 400
request POST /backoffice/account_list/delete "{\"user_id\":$NEW_ID}"
expect_status "valid (the spec user)" 200
expect_json "valid" .success 1
request GET /backoffice/account_list/findall
expect_eq "valid: the user is gone" 0 "$(printf '%s' "$BODY" | jq --arg e "$NEW" '[.users[] | select(.email==$e)] | length')"

spec_name "account_login/forgottenpassword"
request POST /backoffice/account_login/forgottenpassword '{"email":"nobody-'"$STAMP"'@example.com"}'
expect_status "invalid (unknown address, no mail is sent)" 400
expect_json "invalid" .message "Your email address does not exist"
request POST /backoffice/account_login/forgottenpassword '{}'
expect_status "invalid (empty object)" 400
# the valid case resets a password and sends a mail, so it is covered by the mail spec on an instance with an SMTP sink

spec_name "account_login/post"
request POST /backoffice/account_login/post "{\"email\":\"$SPEC_EMAIL\",\"password\":\"$SPEC_PASSWORD\"}"
expect_status "valid" 200
expect_json "valid" .success 1
request POST /backoffice/account_login/post "{\"email\":\"$SPEC_EMAIL\",\"password\":\"wrong-$STAMP\"}"
expect_status "invalid (wrong password)" 400
expect_json "invalid" .error 1
request POST /backoffice/account_login/post '{"email":["a"],"password":"x"}'
expect_status "invalid (email is not a string)" 400
request POST /backoffice/account_login/post '{}'
expect_status "invalid (empty object, was 404)" 400

spec_name "account_login/logout"
login
request GET /backoffice/account_login/logout
expect_status "valid" 200
expect_json "valid" .success true
request GET /backoffice/account_list/findall
expect_eq "after logout the backoffice gives no user data" "" "$(json .users)"

spec_done
