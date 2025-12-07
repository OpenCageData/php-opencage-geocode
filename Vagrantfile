# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://vagrantcloud.com/search.
  config.vm.box = "bento/ubuntu-24.04"
  if RUBY_PLATFORM.include?('darwin') && RUBY_PLATFORM.include?('arm64')
    # Apple Silicon/M processor
    config.vm.box = 'gutehall/ubuntu24-04'
  end

  config.vm.synced_folder ".", "/home/vagrant/php-opencage-geocode"

  # Enable provisioning with a shell script. Additional provisioners such as
  # Puppet, Chef, Ansible, Salt, and Docker are also available. Please see the
  # documentation for more information about their specific syntax and use.
  config.vm.provision "shell", inline: <<-SHELL
    apt-get update -qq
    apt-get install -y -qq software-properties-common gnupg2 unzip
    add-apt-repository -y ppa:ondrej/php
    apt-get update -qq
    apt-get install -y -qq php8.3-cli php8.3-curl php8.3-xml php8.3-mbstring
    apt-get install -y -qq php8.4-cli php8.4-curl php8.4-xml php8.4-mbstring
    apt-get install -y -qq php8.5-cli php8.5-curl php8.5-xml php8.5-mbstring
    curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
    cd /home/vagrant/php-opencage-geocode
    composer install
    # run tests:
    ./vendor/bin/phpunit
    ./vendor/bin/phpcs .
  SHELL
end
