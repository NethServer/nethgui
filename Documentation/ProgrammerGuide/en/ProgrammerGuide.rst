=======================
   NethGui Framework
=======================
------------------
 Programmer Guide
------------------


This guide is addressed to the Programmer (you) who wants to add new
functions to NethGui.  It shows how to achieve this goal, implementing
a Module.

Modules in NethGui constitute the functional part of the System.  The
Programmer achieves the wished behaviour

* by mapping input data to proper values into Host Configuration
  Database (see `Parameters and Adapters`_), or by processing input data
  in some other way;

* through Modules composition, breaking down the functionalities and
  delegating them to sub-Modules (see `Module Composition`_);

* through building the Module user interface, (see `Templates`_).

The Framework is provided with a `basic testing class`_ to easily verify
the Module behaviour.

Implementing a Module
=====================

In this section we will write a simple Module that controls the
enabled/disabled state of an hypothetical *OnOffService*. 

The state of the service is defined in the Host Configuration
Database, by the value of ``status`` property in key ``onoff`` of
``myconf`` database. So we initialize the required prop to
``disabled`` with the following shell command::

  # /sbin/e-smith/db myconf set onoff service status disabled

To implement a Module you should extend
`NethGui_Core_Module_Standard`_ class. So we create a new PHP file
under ``NethGui/Module/`` subdirectory: ``OnOffService.php``.

In ``OnOffService.php`` we write::

   <?php

   class NethGui_Module_OnOffService extends NethGui_Core_Module_Standard {

      public function initialize()
      {
          parent::initialize();     // base class implementation call

	  // Declare serviceStatus parameter and link it to
	  // status prop in onff key of myconf database.
          $this->declareParameter(
	      'serviceStatus',                     // parameter name 
	      '/^(enabled|disabled)$/',            // regexp validation
	      array('myconf', 'onoff', 'status')   // parameter - prop binding
	  );
      }

   } // End of class.

   // PHP closing tag omitted.

Things to note down here are:

* No PHP ``require`` commands are needed to load
  ``NethGui_Core_Module_Standard``, as the file path is given
  implicitly in the class name, substituting underscores ``_`` with
  slashes ``/``.

* We re-implement initialize_ method to declare a Module parameter

  - The parameter name is ``serviceStatus``

  - The parameter value must match ``/^(enabled|disabled)$/`` `regular
    expression`_ to be considered valid.

  - The parameter value, if valid, is written to prop ``status`` of
    key ``onoff`` in ``myconf`` database.

The OnOffModule is now fully functional, as the Standard implementation provides transferring the parameter to/from database value, if it is correctly validated.

Moreover the parameter is transferred to the View layer, so that Module PHP Template can show it in HTML.  

Of course, we have to write the Template first. Now, we create another PHP file under ``NethGui/View/`` directory, ``NethGui_View_OnOffService.php``::

   <h1>OnOffService</h1>
   
   TODO: complete the example after UI widget are defined. See issue #23.



.. _basic testing class: 

Module Testing
^^^^^^^^^^^^^^






.. _NethGui_Core_Module_Standard: http://nethgui.nethesis.it/docs/NethGui/Core/NethGui_Core_Module_Standard.html
.. _initialize: http://nethgui.nethesis.it/docs/NethGui/Core/NethGui_Core_Module_Standard.html#initialize
.. _regular expression: http://php.net/manual/en/function.preg-match.php


Templates
=========

T

Parameters and Adapters
=======================

A

Module Composition
==================

C












