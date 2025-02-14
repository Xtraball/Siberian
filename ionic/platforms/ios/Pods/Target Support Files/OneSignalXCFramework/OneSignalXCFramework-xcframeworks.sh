#!/bin/sh
set -e
set -u
set -o pipefail

function on_error {
  echo "$(realpath -mq "${0}"):$1: error: Unexpected failure"
}
trap 'on_error $LINENO' ERR


# This protects against multiple targets copying the same framework dependency at the same time. The solution
# was originally proposed here: https://lists.samba.org/archive/rsync/2008-February/020158.html
RSYNC_PROTECT_TMP_FILES=(--filter "P .*.??????")


variant_for_slice()
{
  case "$1" in
  "OneSignalFramework.xcframework/ios-arm64")
    echo ""
    ;;
  "OneSignalFramework.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "maccatalyst"
    ;;
  "OneSignalFramework.xcframework/ios-arm64_x86_64-simulator")
    echo "simulator"
    ;;
  "OneSignalCore.xcframework/ios-arm64")
    echo ""
    ;;
  "OneSignalCore.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "maccatalyst"
    ;;
  "OneSignalCore.xcframework/ios-arm64_x86_64-simulator")
    echo "simulator"
    ;;
  "OneSignalExtension.xcframework/ios-arm64")
    echo ""
    ;;
  "OneSignalExtension.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "maccatalyst"
    ;;
  "OneSignalExtension.xcframework/ios-arm64_x86_64-simulator")
    echo "simulator"
    ;;
  "OneSignalInAppMessages.xcframework/ios-arm64")
    echo ""
    ;;
  "OneSignalInAppMessages.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "maccatalyst"
    ;;
  "OneSignalInAppMessages.xcframework/ios-arm64_x86_64-simulator")
    echo "simulator"
    ;;
  "OneSignalLiveActivities.xcframework/ios-arm64")
    echo ""
    ;;
  "OneSignalLiveActivities.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "maccatalyst"
    ;;
  "OneSignalLiveActivities.xcframework/ios-arm64_x86_64-simulator")
    echo "simulator"
    ;;
  "OneSignalLocation.xcframework/ios-arm64")
    echo ""
    ;;
  "OneSignalLocation.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "maccatalyst"
    ;;
  "OneSignalLocation.xcframework/ios-arm64_x86_64-simulator")
    echo "simulator"
    ;;
  "OneSignalNotifications.xcframework/ios-arm64")
    echo ""
    ;;
  "OneSignalNotifications.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "maccatalyst"
    ;;
  "OneSignalNotifications.xcframework/ios-arm64_x86_64-simulator")
    echo "simulator"
    ;;
  "OneSignalOSCore.xcframework/ios-arm64")
    echo ""
    ;;
  "OneSignalOSCore.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "maccatalyst"
    ;;
  "OneSignalOSCore.xcframework/ios-arm64_x86_64-simulator")
    echo "simulator"
    ;;
  "OneSignalOutcomes.xcframework/ios-arm64")
    echo ""
    ;;
  "OneSignalOutcomes.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "maccatalyst"
    ;;
  "OneSignalOutcomes.xcframework/ios-arm64_x86_64-simulator")
    echo "simulator"
    ;;
  "OneSignalUser.xcframework/ios-arm64")
    echo ""
    ;;
  "OneSignalUser.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "maccatalyst"
    ;;
  "OneSignalUser.xcframework/ios-arm64_x86_64-simulator")
    echo "simulator"
    ;;
  esac
}

archs_for_slice()
{
  case "$1" in
  "OneSignalFramework.xcframework/ios-arm64")
    echo "arm64"
    ;;
  "OneSignalFramework.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "arm64 x86_64"
    ;;
  "OneSignalFramework.xcframework/ios-arm64_x86_64-simulator")
    echo "arm64 x86_64"
    ;;
  "OneSignalCore.xcframework/ios-arm64")
    echo "arm64"
    ;;
  "OneSignalCore.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "arm64 x86_64"
    ;;
  "OneSignalCore.xcframework/ios-arm64_x86_64-simulator")
    echo "arm64 x86_64"
    ;;
  "OneSignalExtension.xcframework/ios-arm64")
    echo "arm64"
    ;;
  "OneSignalExtension.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "arm64 x86_64"
    ;;
  "OneSignalExtension.xcframework/ios-arm64_x86_64-simulator")
    echo "arm64 x86_64"
    ;;
  "OneSignalInAppMessages.xcframework/ios-arm64")
    echo "arm64"
    ;;
  "OneSignalInAppMessages.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "arm64 x86_64"
    ;;
  "OneSignalInAppMessages.xcframework/ios-arm64_x86_64-simulator")
    echo "arm64 x86_64"
    ;;
  "OneSignalLiveActivities.xcframework/ios-arm64")
    echo "arm64"
    ;;
  "OneSignalLiveActivities.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "arm64 x86_64"
    ;;
  "OneSignalLiveActivities.xcframework/ios-arm64_x86_64-simulator")
    echo "arm64 x86_64"
    ;;
  "OneSignalLocation.xcframework/ios-arm64")
    echo "arm64"
    ;;
  "OneSignalLocation.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "arm64 x86_64"
    ;;
  "OneSignalLocation.xcframework/ios-arm64_x86_64-simulator")
    echo "arm64 x86_64"
    ;;
  "OneSignalNotifications.xcframework/ios-arm64")
    echo "arm64"
    ;;
  "OneSignalNotifications.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "arm64 x86_64"
    ;;
  "OneSignalNotifications.xcframework/ios-arm64_x86_64-simulator")
    echo "arm64 x86_64"
    ;;
  "OneSignalOSCore.xcframework/ios-arm64")
    echo "arm64"
    ;;
  "OneSignalOSCore.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "arm64 x86_64"
    ;;
  "OneSignalOSCore.xcframework/ios-arm64_x86_64-simulator")
    echo "arm64 x86_64"
    ;;
  "OneSignalOutcomes.xcframework/ios-arm64")
    echo "arm64"
    ;;
  "OneSignalOutcomes.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "arm64 x86_64"
    ;;
  "OneSignalOutcomes.xcframework/ios-arm64_x86_64-simulator")
    echo "arm64 x86_64"
    ;;
  "OneSignalUser.xcframework/ios-arm64")
    echo "arm64"
    ;;
  "OneSignalUser.xcframework/ios-arm64_x86_64-maccatalyst")
    echo "arm64 x86_64"
    ;;
  "OneSignalUser.xcframework/ios-arm64_x86_64-simulator")
    echo "arm64 x86_64"
    ;;
  esac
}

copy_dir()
{
  local source="$1"
  local destination="$2"

  # Use filter instead of exclude so missing patterns don't throw errors.
  echo "rsync --delete -av "${RSYNC_PROTECT_TMP_FILES[@]}" --links --filter \"- CVS/\" --filter \"- .svn/\" --filter \"- .git/\" --filter \"- .hg/\" \"${source}*\" \"${destination}\""
  rsync --delete -av "${RSYNC_PROTECT_TMP_FILES[@]}" --links --filter "- CVS/" --filter "- .svn/" --filter "- .git/" --filter "- .hg/" "${source}"/* "${destination}"
}

SELECT_SLICE_RETVAL=""

select_slice() {
  local xcframework_name="$1"
  xcframework_name="${xcframework_name##*/}"
  local paths=("${@:2}")
  # Locate the correct slice of the .xcframework for the current architectures
  local target_path=""

  # Split archs on space so we can find a slice that has all the needed archs
  local target_archs=$(echo $ARCHS | tr " " "\n")

  local target_variant=""
  if [[ "$PLATFORM_NAME" == *"simulator" ]]; then
    target_variant="simulator"
  fi
  if [[ ! -z ${EFFECTIVE_PLATFORM_NAME+x} && "$EFFECTIVE_PLATFORM_NAME" == *"maccatalyst" ]]; then
    target_variant="maccatalyst"
  fi
  for i in ${!paths[@]}; do
    local matched_all_archs="1"
    local slice_archs="$(archs_for_slice "${xcframework_name}/${paths[$i]}")"
    local slice_variant="$(variant_for_slice "${xcframework_name}/${paths[$i]}")"
    for target_arch in $target_archs; do
      if ! [[ "${slice_variant}" == "$target_variant" ]]; then
        matched_all_archs="0"
        break
      fi

      if ! echo "${slice_archs}" | tr " " "\n" | grep -F -q -x "$target_arch"; then
        matched_all_archs="0"
        break
      fi
    done

    if [[ "$matched_all_archs" == "1" ]]; then
      # Found a matching slice
      echo "Selected xcframework slice ${paths[$i]}"
      SELECT_SLICE_RETVAL=${paths[$i]}
      break
    fi
  done
}

install_xcframework() {
  local basepath="$1"
  local name="$2"
  local package_type="$3"
  local paths=("${@:4}")

  # Locate the correct slice of the .xcframework for the current architectures
  select_slice "${basepath}" "${paths[@]}"
  local target_path="$SELECT_SLICE_RETVAL"
  if [[ -z "$target_path" ]]; then
    echo "warning: [CP] $(basename ${basepath}): Unable to find matching slice in '${paths[@]}' for the current build architectures ($ARCHS) and platform (${EFFECTIVE_PLATFORM_NAME-${PLATFORM_NAME}})."
    return
  fi
  local source="$basepath/$target_path"

  local destination="${PODS_XCFRAMEWORKS_BUILD_DIR}/${name}"

  if [ ! -d "$destination" ]; then
    mkdir -p "$destination"
  fi

  copy_dir "$source/" "$destination"
  echo "Copied $source to $destination"
}

install_xcframework "${PODS_ROOT}/OneSignalXCFramework/iOS_SDK/OneSignalSDK/OneSignal_XCFramework/OneSignalFramework.xcframework" "OneSignalXCFramework/OneSignal" "framework" "ios-arm64" "ios-arm64_x86_64-maccatalyst" "ios-arm64_x86_64-simulator"
install_xcframework "${PODS_ROOT}/OneSignalXCFramework/iOS_SDK/OneSignalSDK/OneSignal_Core/OneSignalCore.xcframework" "OneSignalXCFramework/OneSignalCore" "framework" "ios-arm64" "ios-arm64_x86_64-maccatalyst" "ios-arm64_x86_64-simulator"
install_xcframework "${PODS_ROOT}/OneSignalXCFramework/iOS_SDK/OneSignalSDK/OneSignal_Extension/OneSignalExtension.xcframework" "OneSignalXCFramework/OneSignalExtension" "framework" "ios-arm64" "ios-arm64_x86_64-maccatalyst" "ios-arm64_x86_64-simulator"
install_xcframework "${PODS_ROOT}/OneSignalXCFramework/iOS_SDK/OneSignalSDK/OneSignal_InAppMessages/OneSignalInAppMessages.xcframework" "OneSignalXCFramework/OneSignalInAppMessages" "framework" "ios-arm64" "ios-arm64_x86_64-maccatalyst" "ios-arm64_x86_64-simulator"
install_xcframework "${PODS_ROOT}/OneSignalXCFramework/iOS_SDK/OneSignalSDK/OneSignal_LiveActivities/OneSignalLiveActivities.xcframework" "OneSignalXCFramework/OneSignalLiveActivities" "framework" "ios-arm64" "ios-arm64_x86_64-maccatalyst" "ios-arm64_x86_64-simulator"
install_xcframework "${PODS_ROOT}/OneSignalXCFramework/iOS_SDK/OneSignalSDK/OneSignal_Location/OneSignalLocation.xcframework" "OneSignalXCFramework/OneSignalLocation" "framework" "ios-arm64" "ios-arm64_x86_64-maccatalyst" "ios-arm64_x86_64-simulator"
install_xcframework "${PODS_ROOT}/OneSignalXCFramework/iOS_SDK/OneSignalSDK/OneSignal_Notifications/OneSignalNotifications.xcframework" "OneSignalXCFramework/OneSignalNotifications" "framework" "ios-arm64" "ios-arm64_x86_64-maccatalyst" "ios-arm64_x86_64-simulator"
install_xcframework "${PODS_ROOT}/OneSignalXCFramework/iOS_SDK/OneSignalSDK/OneSignal_OSCore/OneSignalOSCore.xcframework" "OneSignalXCFramework/OneSignalOSCore" "framework" "ios-arm64" "ios-arm64_x86_64-maccatalyst" "ios-arm64_x86_64-simulator"
install_xcframework "${PODS_ROOT}/OneSignalXCFramework/iOS_SDK/OneSignalSDK/OneSignal_Outcomes/OneSignalOutcomes.xcframework" "OneSignalXCFramework/OneSignalOutcomes" "framework" "ios-arm64" "ios-arm64_x86_64-maccatalyst" "ios-arm64_x86_64-simulator"
install_xcframework "${PODS_ROOT}/OneSignalXCFramework/iOS_SDK/OneSignalSDK/OneSignal_User/OneSignalUser.xcframework" "OneSignalXCFramework/OneSignalUser" "framework" "ios-arm64" "ios-arm64_x86_64-maccatalyst" "ios-arm64_x86_64-simulator"

