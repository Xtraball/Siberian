#!/usr/bin/env ruby
# Happy path for bin/scripts (Patch): open the real iOS project, apply
# Settings.alter_project and print the sha256 of the saved project.pbxproj.
# Compared with golden/xcodeproj-alter.sha256 so a gem bump (xcodeproj, rexml)
# must produce a byte-identical project file.
#
#   ruby xcodeproj_happy_path.rb <repo-root> <scripts-dir>
require 'bundler'
require 'digest'
require 'fileutils'
require 'tmpdir'

repo, scripts = ARGV
Bundler.require(:default)
load File.join(scripts, 'Loggy')
load File.join(scripts, 'Settings')

Dir.mktmpdir do |dir|
  FileUtils.cp_r(File.join(repo, 'ionic/platforms/ios/AppsMobileCompany.xcodeproj'), dir)
  Settings.alter_project("#{dir}/")
  pbx = File.join(dir, 'AppsMobileCompany.xcodeproj/project.pbxproj')
  text = File.read(pbx)
  abort('entitlements not added') unless text.include?('CODE_SIGN_ENTITLEMENTS')
  # xcodeproj derives object UUIDs differently across releases (and sorts
  # sections by them), so compare content with UUIDs masked and lines sorted.
  masked = text.gsub(/\b[0-9A-F]{24}\b/, 'UUID').lines.sort.join
  puts "SHA256 #{Digest::SHA256.hexdigest(masked)}"
end
