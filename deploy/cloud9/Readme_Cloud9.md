# Developing on Cloud9 #

## Getting Started ##

### Upgrading Ansible ###
You will need to upgrade ansible to version 1.9.3, the default installed on Cloud9 is 1.5.x.  You can do the upgrade with pip.  In the bash window:

````
pip install ansible --upgrade
````

You can check the ansible version with:

````
ansible --version
````
The installed version should be `ansible 1.9.3`.

### Getting the Code ###
You'll want the code up to date

````
git checkout master-sbu
git pull origin
````

## Installing via Ansible ##

````
cd ~/workspace/deploy
ansible-playbook -i hosts playbook_cloud9.yml --limit localhost -K
````

Press enter (no password) at the sudo prompt, and wait for ansible to complete.

### What could go wrong? ###

1. postfix

    * Could have a master pid file hanging around in `/var/spool/postfix/pid`, if its there remove it.
    * Let Cambell know. Its hard to reproduce on cloud9 without starting over.

1. mongo





