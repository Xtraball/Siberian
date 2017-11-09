#!/usr/bin/env ruby
require 'xcodeproj'
require 'pp'

platform_path = ARGV[0]

projectpath = platform_path + "AppsMobileCompany.xcodeproj"
puts "Opening " + platform_path
proj = Xcodeproj::Project.open(projectpath)
entitlement_path = "AppsMobileCompany/Entitlements-$(CONFIGURATION).plist"

group_name= proj.root_object.main_group.name

file = proj.new_file(entitlement_path.gsub("$(CONFIGURATION)", "Debug"))
file_release = proj.new_file(entitlement_path.gsub("$(CONFIGURATION)", "Release"))

attributes = {}
proj.targets.each do |target|
    attributes[target.uuid] = {
        "ProvisioningStyle" => "Manual",
        "SystemCapabilities" => {
            "com.apple.Push" => {
                "enabled" => 1
            }
        }
    }
    target.add_file_references([file, file_release])
    puts "Added to target: " + target.uuid
end
proj.root_object.attributes['TargetAttributes'] = attributes

proj.build_configurations.each do |config|
    config.build_settings.store("CODE_SIGN_ENTITLEMENTS", entitlement_path.gsub("$(CONFIGURATION)", config.name))
    config.build_settings.store("CODE_SIGN_ENTITLEMENTS[sdk*]", entitlement_path.gsub("$(CONFIGURATION)", config.name))
end
puts "Added entitlements file path: " + entitlement_path

proj.save