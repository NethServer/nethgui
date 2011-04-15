=======================
   NethGui Framework
=======================
------------------
 Programmer Guide
------------------

.. sectnum:: 

This guide is addressed to the Programmer (you) who wants to add new
functions to NethGui.  It shows how to achieve this goal, implementing
a Module using different techniques.

Modules in NethGui constitute the functional part of the System.  The
Programmer achieves the wished behaviour

* by mapping input data to proper values into Host Configuration
  Database (see `Parameters`_ and `Adapters`_), or by processing input data
  in some other way;

* through Modules composition, breaking down the functionalities and
  delegating them to sub-Modules (see `Module Composition`_);

The Framework is provided with a `basic testing class`_ to easily verify
the Module behaviour.

A Module is associated to its View, which represents the user
interface abstraction.  Such abstraction is translated into HTML code
by providing a Template_ or a callback method (see `View layer`_ for
details). A Module receives also an `host configuration database`_
object, to store and retrieve values and trigger events.


.. _host configuration database: 

.. contents:: :depth: 2


Module dissection
=================

A Module must implement a set of well-known methods defined by
`NethGui_Core_ModuleInterface`_.  

Every module extending `NethGui_Core_Module_Standard`_ class inherits
these implementations for free.  From now on, if not otherwise stated,
we will refer to this class as the "basic class" or "basic
implementation".

The Framework calls these methods at some point during
execution time in a fixed order:

Initialization phase 
    When initialize_ is called, the Module is ready to use the
    database object (see getHostConfiguration_). You can declare here
    what are the Parameters_ of the Module, and how they are connected
    to the database through Adapters_ (see declareParameter_).

Request handling phase 
    bind_ receives the input parameters and can store their values in
    the internal state of the Module. validate_ checks if parameter
    values are correct and signals if an error occurs.  process_
    persists necessary changes to the database.

Rendering phase 
    prepareView_ transfers the module internal state and
    necessary database values to the view state.


.. _getHostConfiguration: http://nethgui.nethesis.it/Documentation/Api/Core/Module/NethGui_Core_Module_Standard.html#getHostConfiguration



Parameters
----------

Parameters are the subject of data exchanges between the host
configuration database and the view layer. The basic implementation
executes all the data transfers so all you need to use a parameter is
declare it into ``initialize()`` method::

   $this->declareParameter('myParameter');

After a parameter is declared, you can assign to it a value. For
instance, in process_ you can write::

   $this->parameters['myParameter'] = 'myValue';

Later on, that value will be transferred to the view layer; if the
User changes the value through the user interface and sends it back,
you will get the new value, after bind_ is called. So before executing
the previous assignment you can read the value::

   $userInput = $this->parameters['myParameter'];

   if($userInput == '') {
      $this->parameters['myParameter'] = 'myValue';
   }


   
Validators
----------

When a parameter is declared, you can ask the basic class to check its
value against a validation rule.

The second argument to ``declareParameter()`` method defines that rule. It can be of different types.

*Integer*
   Represents a pre-defined validation rule.  basic class defines a set
   of constants. See `NethGui_Core_Module_Standard`_ documentation for
   a complete list.

*String*
   Represents a PERL-compatible regular expression. See PHP
   `Perl-Compatible Regular Expression`_ syntax for details.

*NethGui_Core_Validator* object
   Passing an object of `NethGui_Core_Validator`_ class is the most
   expressive choice: you can specify arguments to validation rules
   and also combine them as OR expressions.

For instance to declare a ``myIpAddress`` parameter that must match a
string representing a valid IPV4 address type::

   // 2nd argument is of type integer. Using a predefined constant.
   $this->declareParameter('myIpAddress1', self::VALID_IPV4_ADDRESS);

   // 2nd argument is of type string, indicating a regular expression based validator.
   // This is not as good as the integer constant in case 1.
   $this->declareParameter('myIpAddress2', '/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/');

   // 2nd argument is of type Validator. The integer constant of case 1 is
   // shortcut that does exactly the same.
   $this->declareParameter('myIpAddress3', $this->getValidator()->ipv4());

TODO


.. _`NethGui_Core_Validator`: TODO
.. _`Perl-Compatible Regular Expression`: http://www.php.net/manual/en/pcre.pattern.php

Adapters
--------

You have seen in the Parameters_ section how to declare a Parameter
that holds a value. The value was tranferred to and from the view
layer. In this section we will see how to store and retrieve the
parameter value in the host configuration database through Adapters.

All the magic is in the ``declareParameter()`` call. This time we
consider its third argument. It can be of two types:

Array 
   You can use an array to map the parameter value to one or more
   database values. See the examples below to see how to do that.
   This is a shortcut form that leaves the creation and
   initialization of the underlying Adapter object to the basic class.

Nethgui_Core_AdapterInterface implementing object
   You can also build and initialize the Adapter object explicitly or
   obtain it by some other way.  See getAdapter_ method. 

TODO

.. _bind:
.. _validate:
.. _process:
.. _NethGui_Core_ModuleInterface: http://nethgui.nethesis.it/Documentation/Api/Core/NethGui_Core_ModuleInterface.html
.. _getAdapter:
.. _NethGui_Core_Module_Standard: http://nethgui.nethesis.it/Documentation/Api/Core/Module/NethGui_Core_Module_Standard.html
.. _NethGui_Core_Module_Composite: http://nethgui.nethesis.it/Documentation/Api/Core/Module/NethGui_Core_Module_Composite.html



Module composition
==================

TODO; how to split a Module into sub-modules.



View layer
==========

After the processing phase the Framework asks our Module to fill a
View object with the output data. The Module receives a View object as
first argument to prepareView_ method::

   public function prepareView(NethGui_Core_ViewInterface $view, $mode) 
   {
       parent::prepareView($view, $mode);
   }

Basic implementation transfers all module parameters and invariants to
the view object.

A View object resembles a PHP array, where you can store data using
keys and values; indeed a View implements ArrayAccess_ and
IteratorAggregate_ interfaces.

What about ``$mode`` argument? TODO: explain $mode argument.

Later on the view object is rendered, calling a Template_ or a
`Callback method`_.

.. _ArrayAccess: http://php.net/manual/en/class.arrayaccess.php
.. _IteratorAggregate: http://php.net/manual/en/class.iteratoraggregate.php
.. _prepareView: http://nethgui.nethesis.it/Documentation/Api/Core/Module/NethGui_Core_Module_Standard.html#prepareView



Template
--------

The View layer guesses the PHP Template to 

::

   class NethGui_Module_MyModule extends NethGui_Core_Module_Standard 
   {

     .
     .
     .

     public function prepareView(NethGui_Core_ViewInterface $view, $mode) 
     {
         parent::prepareView($view, $mode);
  
         // Use NethGui/View/AlternativeTemplate.php
         // instead of NethGui/View/MyModule.php
         $view->setTemplate("NethGui_View_AlternativeTemplate");
     }

     .
     .
     .
     


Callback method
---------------

Describe how to configure a callback method for a View


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
          parent::initialize();     // basic class implementation call

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

In ``initialize()`` body we declare a parameter, calling declareParameter_:
  
- the parameter name is ``serviceStatus``;
    
- the parameter value must match ``/^(enabled|disabled)$/`` `regular expression`_ to be considered valid;
    
- the parameter value, if valid, is written to prop ``status`` of key ``onoff`` in ``myconf`` database.

The OnOffModule class is now fully functional, as the basic class
implementation provides transferring the parameter to/from database
value, if it is correctly validated.

Moreover the basic class transfers the parameter value to the `View
layer`_, so that we can put it in HTML format through a Template.

Of course, we have to write the Template first, so we create another
PHP file, this time under ``NethGui/View/`` directory,
``NethGui_View_OnOffService.php``::

   <h1>OnOffService</h1>
   
   TODO: complete the example after UI widget are defined. See issue #23.

.. _ModuleTestCase: 
.. _basic testing class: http://nethgui.nethesis.it/docs/Tests/ModuleTestCase.html
.. _NethGui_Core_Module_Standard: http://nethgui.nethesis.it/Documentation/Api/Core/Module/NethGui_Core_Module_Standard.html
.. _NethGui_Core_Module_Composite: http://nethgui.nethesis.it/Documentation/Api/Core/Module/NethGui_Core_Module_Composite.html
.. _initialize: http://nethgui.nethesis.it/Documentation/Api/Core/Module/NethGui_Core_Module_Standard.html#initialize
.. _declareParameter: http://nethgui.nethesis.it/Documentation/Api/Core/Module/NethGui_Core_Module_Standard.html#declareParameter
.. _regular expression: http://php.net/manual/en/function.preg-match.php


Module Testing
==============

In `our example`_ we must test OnOffService in three scenarios:

1. The User turns the service ON.

2. The User turns the service OFF.

3. The User takes no action.

We can check if OnOffService module is correct by writing a
PHPUnit_ test case. NethGui comes with a basic class to be extended to
build module tests upon it: ModuleTestCase_.

As we are testing a module, we put our test case class under
``Tests/Unit/NethGui/Module/`` directory; the class file name must be
ending with ``Test.php``.

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

Consider the body of ``testTurnOn()`` method.  To run the test
procedure we first set up three member variables:

* moduleParameters_

* expectedView_

* expectedDb_

In moduleParameters_ we assign to each parameter the corresponding
input value.

In expectedView_ we prepare an array of couples ``<name, value>``.
The module is expected to transfer to the View layer exactly that list
of values in that order.

In expectedDb_ we specify the list of low level database calls the
module must execute.

.. _PHPUnit: http://www.phpunit.de/manual/3.5/en/index.html
.. _expectedDb: http://nethgui.nethesis.it/Documentation/Api/Tests/ModuleTestCase.html#$expectedDb
.. _expectedView: http://nethgui.nethesis.it/Documentation/Api/Tests/ModuleTestCase.html#$expectedView
.. _moduleParameters: http://nethgui.nethesis.it/Documentation/Api/Tests/ModuleTestCase.html#$moduleParameters
.. _our example: `Implementing a simple Module`_

