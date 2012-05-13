# Audit Plugin

This is a CakePHP 2 plugin. This package contains 2 Behavior classes to help automate the auditing of your models: AuditRevision and AuditTrail. AuditTrail allows you to pick several key fields and track their changes throughout the lifetime of that model. AuditRevision is intended to create a complete version history of the models created within the application.

## Installation

At present, I haven't created a download zip for this project yet as I'm still considering rapidly adding features. If you wish to start using this project, after running `git clone git://github.com/connrs/Audit.cakephp.git` make sure that you perform `git submodule update --init` eg:

    git clone git://github.com/connrs/Audit.cakephp.git app/Plugin/Audit
    cd app/Plugin/Audit
    git submodule update --init

Once I've completed further changes, I will ensure that versioning occurs with nice downloads. I promise.

## Audit Trail

With your model class, add the following your your actsAs array: Audit.AuditTrail

    public $actsAs = array('Audit.AuditTrail');

The AuditTrail Behavior class makes some basic assumptions about your CakePHP application that should mean that, for most applications you don't need to add any extra configuration. For those with non-standard table setups (ie. you may have a primary key that isn't `id` or use updated instead of modified or if you store user ids with modified\_by/created\_by/updated\_by) you can pass in an array of options which will allow the DataAuditRevision class to correctly generate trail data.

## Audit Revision

With your model class, add the following to your actsAs array: Audit.AuditRevision

    public $actsAs = array('Audit.AuditRevision');

The AuditRevision Behavior class makes some basic assumptions about your CakePHP application, just as with AuditTrail, that means that you can assume that most settings should work for most applications. For those with non-standard table setups, it should still work as standard. An array of options can be passed to configure individual keys.

## Database Requirements

Each model tracked must have a table generated which contains either the fields to be tracked or the entire list of fields (in the case of AuditRevision) along with 3 additional fields. For AuditTrailBehavior they must be:

* trail\_id (primary key, auto incrementing)
* trail\_created (datetime)
* trail\_created\_by (integer)

For AuditRevisionBehavior they must be: 

* revision\_id (primary key, auto incrementing)
* revision\_type (string type, varchar(6) should be enough as it only needs to store update/delete/create)
* revision\_created (datetime)

On the todo list is a set of console commands that will automatically generate schema files that you may then use to create your tables.
