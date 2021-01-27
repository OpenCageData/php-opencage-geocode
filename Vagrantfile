# -*- mode: ruby -*-
# vi: set ft=ruby :

Vagrant.configure("2") do |config|
  # The most common configuration options are documented and commented below.
  # For a complete reference, please see the online documentation at
  # https://docs.vagrantup.com.

  # Every Vagrant development environment requires a box. You can search for
  # boxes at https://vagrantcloud.com/search.
  config.vm.box = "bento/ubuntu-20.04"

  config.vm.synced_folder ".", "/home/vagrant/php-opencage-geocode"

  # Enable provisioning with a shell script. Additional provisioners such as
  # Puppet, Chef, Ansible, Salt, and Docker are also available. Please see the
  # documentation for more information about their specific syntax and use.
  config.vm.provision "shell", inline: <<-SHELL
    apt-get update -qq
    apt-get install -y -qq php-cli php-curl php-xml php-mbstring unzip
    curl -sS https://getcomposer.org/installer | sudo php -- --install-dir=/usr/local/bin --filename=composer
    cd /home/vagrant/php-opencage-geocode
    composer install
    # run tests:
    OPENCAGE_API_KEY=... ./vendor/bin/phpunit
    ./vendor/bin/phpcs .
  SHELL
end
