=======================
   NethGui Framework
=======================
------------------
 Programmer Guide
------------------


This guide is addressed to the Programmer (you) who wants to add new
functions to NethGui.  It shows how to achieve this goal, implementing
a Module using different techniques.

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



Implementing a simple Module
============================

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

   class NethGui_Module_OnOffService extends NethGui_Core_Module_Standard 
   {

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

* We re-implement ``initialize()`` method to declare a Module parameter so we *must* call parent's initialize_.


In our ``initialize()`` body we declare a parameter, calling declareParameter_:
  
- the parameter name is ``serviceStatus``;
    
- the parameter value must match ``/^(enabled|disabled)$/`` `regular expression`_ to be considered valid;
    
- the parameter value, if valid, is written to prop ``status`` of key ``onoff`` in ``myconf`` database.

The OnOffModule class is now fully functional, as the base class
implementation provides transferring the parameter to/from database
value, if it is correctly validated.

Moreover the base class transfers the parameter value to the View
layer, so that we can put it in HTML format through a Template.

Of course, we have to write the Template first, so we create another
PHP file, this time under ``NethGui/View/`` directory,
``NethGui_View_OnOffService.php``::

   <h1>OnOffService</h1>
   
   TODO: complete the example after UI widget are defined. See issue #23.

.. _ModuleTestCase: 
.. _basic testing class: http://nethgui.nethesis.it/docs/Tests/ModuleTestCase.html
.. _NethGui_Core_Module_Standard: http://nethgui.nethesis.it/docs/NethGui/Core/NethGui_Core_Module_Standard.html
.. _initialize: http://nethgui.nethesis.it/docs/NethGui/Core/NethGui_Core_Module_Standard.html#initialize
.. _declareParameter: http://nethgui.nethesis.it/docs/NethGui/Core/NethGui_Core_Module_Standard.html#declareParameter
.. _regular expression: http://php.net/manual/en/function.preg-match.php


Module Testing
^^^^^^^^^^^^^^

In our example we must test OnOffService in three scenarios:

1. The User turns the service ON.

2. The User turns the service OFF.

3. The User takes no action.

We can check if our OnOffService module is correct by writing a
PHPUnit_ test case. NethGui comes with a basic class to be extended to
build module tests upon it: ModuleTestCase_.

As we are testing a module, we put our test case class under ``Tests/Unit/NethGui/Module/`` directory; the class file name must be ending with ``Test.php``.

In ``OnOffServiceTest.php`` we write::

   <?php

   class NethGui_Module_OnOffServiceTest extends ModuleTestCase 
   {
       protected function setUp() 
       {
           parent::setUp(); 
           $this->object = new NethGui_Module_OnOffService();
       }

       public function testTurnOn() 
       {
           // set the input parameter value:
           $this->moduleParameters = array(
              'serviceStatus'=>'enabled'
           );

           $this->expectedView = array(
                // expect a view state with a "serviceStatus" element :
                array('serviceStatus', 'enabled')
           );

           $this->expectedDb = array(

                // expect a getprop call returning "disabled":
                array('myconf', self::DB_GET_PROP, array('onoff', 'status'), 'disabled'),

                // expect a setprop call setting value to "enabled":
                array('myconf', self::DB_SET_PROP, array('onoff', array('status' => 'enabled')), TRUE),
           );

           $this->runModuleTestProcedure();
       }
      
       public function testTurnOff() 
       {
           $this->markTestIncomplete();                      // skip test
       }

       public function testNoAction() 
       {
           $this->markTestIncomplete();                      // skip test
       }

   } // end of class

Consider the body of ``testTurnOn()`` method.  To run the test procedure we first set up three member variables:

* moduleParameters_

* expectedView_

* expectedDb_

In moduleParameters_ we assign to each parameter the corresponding
input value.

In expectedView_ we prepare an array of couples ``<name, value>``.
The module is expected to transfer to the View layer exactly that list
of values in that order.

In expectedDb_ we specify the list of low level database calls the module must execute.

.. _PHPUnit: http://www.phpunit.de/manual/3.5/en/index.html
.. _expectedDb: 
.. _expectedView:
.. _moduleParameters: http://nethgui.nethesis.it/docs/Tests/ModuleTestCase.html


Templates
=========

T

Parameters and Adapters
=======================

A

Module Composition
==================

C












