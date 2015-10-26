# Installation

See [installation instructions](installation.md).

## Getting started

So to do a simple deployment you need to do the following:

 - Add your project to dogpro
 - Add inventory
 - Ensure your servers are accessible by dogpro
 - Add dogpro configuration file to your project

### Adding repositories

First off, you need to add repositories to DogPro, to do it, you need:

 - Go to "New Project" in top or side menu
 - Copy public key from "Deployment key" text area
 - Add it to your GIT (github, gitlab, bitbucket or whatever else you use)
 - Then, enter your repository url (only ssh urls currently supported)
 - Modify name and group if needed and save
 - After a while, you should see the commits in "commits tab"
 
### Adding inventories

After you added repository, you need to add inventories:

 - Go to "inventories" tab in your project
 - Enter inventory name and inventory itself
 - Inventory format is the same as ansible ([read more](http://docs.ansible.com/ansible/intro_inventory.html))
 - Save inventory
 - You should now see public key for inventory, copy it and add it to your server "authorized_keys" file

### Adding dogpro.yml to your project

 - Head to "Documentation" in top menu
 - Select "Create configuration" tab
 - Add services you want to use
 - Customize options in fields
 - Copy generated config and add `dogpro.yml` with this content to your project

### Deploying

 - Go to "commits" tab in your project
 - Click on arrow in commit you want to deploy
 - Select playbooks you want to run
 - Enjoy
