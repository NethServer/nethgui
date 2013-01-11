=======================
   Nethgui framework
=======================
------------------
 Programmer guide
------------------

.. sectnum:: 


This guide is addressed to the Programmer (You) who wants to build a
web user interface for the administration of a GNU/Linux system.

.. warning:: This document refers to an early framework version. It is
             outdated and needs reworking.
             

.. contents:: :depth: 3
.. |date| date:: Last updated %Y-%m-%d %H:%M


          

Overview
========

The Nethgui framework helps with the building of a graphical user
interface for the administration of a GNU/Linux system.

Through that interface the user can change the system configuration --
which is stored in the so-called *Host Configuration Database* -- and
invoke the *events* that apply the configuration to the running
operating system.

On the other side, the interface is constituted of common graphical
controls such as, buttons, checkboxes, radio buttons, text input
fields and so on, that drive the configuration values.

As the Programmer you have to concentrate your work on what the
interface does more than how it looks. Indeed the framework is
centered on the concept of Module: in an hypothetical
*Model-View-Controller* architecture the Module plays the role of the
*Controller*, the Host Configuration Database represent the *Model*
and the View-Template-Renderer triad constitutes the *View* component.


The Host Configuration Database
===============================

The Host Configuration Database is the component providing the storage
of the operating system configuration and the methods to apply it to
the running system.  Out of the boundaries of the Nethgui framework,
this is implemented by the SME Server Configuration Database described
by the SME Server Developers Manual [SMEDEV]_.

Normally you should not need to access directly to the Host
Configuration Database: most of the value manipulation operations
occur transparently through Adapters object. Also the events that
apply the modified configuration to the operating system are signaled
by the framework at the right moment and you should only declare what
event you need to be signaled.

However if you really need to gain a direct access to the Host
Configuration Database have a glance to the `host configuration
interface`_ operations.

What we have to note down here is the **logical organization** of the
values into the database.  More precisely, to talk in terms of the SME
Server vocabulary, we have multiple `databases` where to store our
data.  A database is structured as a two level hash: a `key` can
point to a simple value or an hash itself.  In the latter case we use
the term `prop` to indicate the second level hash key.

To sum up a simple value can be identified

* by its `database` and  `key` names,

* or by its `database`, `key` and `prop` names.

Moreover, in the second case a `type` identifier is assigned to the
key containing the hash. Consider the following example::

   Database1: { ... }
   
   Database2: {
   
      KeyX: "This is a simple string value"

      KeyY <CityCoords>: {
         Lat: 12.913
         Long: 43.910
         City: "Pesaro"
      } 

   }
       
This example show two databases: `Database1` and
`Database2`. Database2 is composed of two keys: `KeyX` holding a
simple string value and `KeyY` which is an hash itself.  The `type` of
KeyY is `CityCoords` and holds three `props`: `Lat`, `Long` and
`City`.
   



.. _`host configuration interface`: ../Api/Core/Nethgui_Core_HostConfigurationInterface.html
.. [SMEDEV] `SME Server Developers Manual`__
__ http://wiki.contribs.org/SME_Server:Documentation:Developers_Manual


Modules
=======

Modules in Nethgui constitute the functional part of your interface,
where the *business rules* reside.  You achieve the wished behaviour

* by mapping input data to proper values into Host Configuration
  Database (see `Parameters`_ and `Adapters`_), or by processing input data
  in some other way;

* through the composition mechanism, breaking down the functionalities and
  delegating them to sub-Modules (see `Module Composition`_);

The framework is provided with a `basic testing class`_ to easily plan
and verify the Module behaviour.

A Module is associated to its View, which represents the user
interface abstraction.  Such abstraction is translated into HTML code
by providing a Template_ script or callback method (see `View layer`_
for details).  A Module receives also an `host configuration database`_
object, to store and retrieve values and trigger events.

.. _`host configuration database`: `The Host Configuration Database`_


Module dissection
-----------------

A Module must implement a set of well-known operations defined by
`Nethgui_Core_ModuleInterface`_ and
`Nethgui_Core_RequestHandlerInterface`_.  Every module extending
`Nethgui_Core_Module_Standard`_ class inherits the implementations of
those operations for free.  From now on, if not otherwise stated, we
will refer to this class as the "basic class" or "basic
implementation".

The framework calls those methods for you at some point during
execution time respecting three phases.  The basic class performs some
common tasks during each phase.

Initialization phase 
    When initialize_ is called, the Module is ready to use the
    database object (see getHostConfiguration_).  You can declare here
    what are the Parameters_ of the Module, and how they are connected
    to the database through Adapters_ (see declareParameter_).

Request handling phase 
    1. bind_ receives the values from the user interface and store
       their values in the internal state of the Module.
    2. validate_ checks if the module internal state is correct
       and signals if any error condition occurs.
    3. process_ persists necessary changes to the database.

Rendering phase 
    prepareView_ transfers the module internal state and
    necessary database values to the view state.  Later on, the view is 
    transformed into XHTML by Templates, possibly through the 
    help of Renderer and Widget objects.


.. _getHostConfiguration: ../Api/Core/Module/Nethgui_Core_Module_Standard.html#getHostConfiguration
.. _Nethgui_Core_RequestHandlerInterface: ../Api/Core/Nethgui_Core_RequestHandlerInterface.html




Parameters
^^^^^^^^^^

The basic implementation holds the module state into a collection of
Parameters which are exchanged between the Host Configuration
Database, the Module, and the View layer.

You can receive a value coming from the View into a module Parameter
simply by declaring it into the ``initialize()`` method::

   $this->declareParameter('myParameter');

Indeed, the actual value coming from the View is stored into the
parameter during by the basic ``bind()`` implementation.

Once a parameter is declared, you can also assign a value to it. For
instance, in process_ you can type::

   $this->parameters['myParameter'] = 'myValue';

Later on, the string ``'myValue'`` will be transferred back to the
View layer.  If the User changes the value through the user interface
and sends it back again, you will get the changed value.

In the `View layer`_ section you will see how to render a UI control
that changes the parameter value.  In our example a text input field
would fit well.


   
Validators
^^^^^^^^^^

When a parameter is declared, you can ask the basic class to verify
that the actual value respects a given validation rule.

The second argument to the ``declareParameter()`` method indicates
this rule. It can be of different data types.

*Integer* 
   Represents a pre-defined validation rule.  The basic class defines
   a set of integer constants.  See `Nethgui_Core_Module_Standard`_
   documentation for a complete list.

*String*
   Represents a PERL-compatible regular expression.  See PHP
   `Perl-Compatible Regular Expression`_ syntax for details.

*Nethgui_Core_Validator* object
   Passing an object of `Nethgui_Core_Validator`_ class is the most
   flexible choice: you can specify arguments to validation rules
   and also combine them as *OR* expressions.

For instance, to declare a ``myIpAddress`` parameter that must match a
string representing a valid IPV4 address, you may type alternatively::

   // 2nd argument is of type integer. Using a predefined constant.
   $this->declareParameter('myIpAddress1', self::VALID_IPV4_ADDRESS);

   // 2nd argument is of type string, indicating a regular expression based validator.
   // This is not as good as the integer constant in case 1: no integer range checks
   $this->declareParameter('myIpAddress2', '/(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})/');

   // 2nd argument is of type Validator. The integer constant of case 1 is
   // a shortcut that does exactly the same.
   $this->declareParameter('myIpAddress3', $this->getValidator()->ipv4());


.. _`Nethgui_Core_Validator`: ../Api/Core/Nethgui_Core_Validator.html
.. _`Perl-Compatible Regular Expression`: http://www.php.net/manual/en/pcre.pattern.php

Adapters
^^^^^^^^

You have seen in the Parameters_ section how to declare a Parameter
that holds a value.  The value was tranferred to and from the View
layer.  In this section we will see how to store and retrieve the
parameter value in the Host Configuration Database through Adapters.

All the magic that instantiates an Adapter for a Parameter is done in
the third argument to the ``declareParameter()`` method.  It can be of
the following types:

*Array*
   You can use an array to map the parameter value to one or more
   database values.  See the examples below to see how to do that.
   This is a shortcut form that leaves the creation and
   initialization of the underlying Adapter object to the basic class.

*Nethgui_Core_AdapterInterface* implementing object
   You can also build and initialize the Adapter object by yourself or
   obtain it by some other way.  See the `host configuration interface`_ 
   for some hints.

Most of the times you should need the Array argument to get an
identity or a mapping adapter.  We will see the two forms in the examples
below.

**Identity adapter**.  Store the domain name in database
`configuration`, key `DomainName`::

  $this->declareParameter(
    'domain',                             // parameter name
    self::VALID_DOMAIN_NAME,              // validator
    array('configuration, 'DomainName')   // identity adapter arguments
  );

An Identity adapter maps a database value into a parameter.

**Map adapter**.  Control an FTP service status (enabled/disabled)
through a single `status` parameter and two database values:

1. *database*: ``configuration``, *key*: ``ftp``, *prop*: ``status``,

2. *database*: ``configuration``, *key*: ``ftp``, *prop*: ``access``.

::

  $this->declareParameter(
    'status',                                   // parameter name
    self:VALID_SERVICE_STATUS,                  // validator
    array(
      array('configuration', 'ftp', 'status'),
      array('configuration', 'ftp', 'access')
    )                                           // mapping adapter arguments
  );

When declaring an adapter the basic implementation searches for two
*converter methods*.  The method names must be prefixed with ``read`` or
``write``, with the full parameter name with the first letter in upper
case following.  So, in our example we must declare two methods for
the ``status`` parameter in the module class, ``readStatus()`` and ``writeStatus()``::

  /**
   * The reader method expects two arguments, in the same order 
   * used during the parameter declaration. The return value 
   * is assigned to the parameter.
   **/
  public function readStatus($status, $access) 
  { 
     .
     .
     .

     return $value;    
  }

  /**
   * The writer method is the dual of the reader: it expects the actual 
   * parameter value as its unique argument and must return an array
   * of database values, in the same order used during the parameter 
   * declaration
   **/
  public function writeStatus($value) 
  {
     .
     .
     .
     return array($status, $access);
  }


.. note:: The converter methods are optional for the Identity adapter,
          but **mandatory** for the Mapping adapter.

The Nethgui framework defines also a Table and an Array adapter that
provide a PHP array interface to the database values.  Those are
closely related to the CRUD scenario implementation thus are discussed
in `The Table Controller`_ section.

.. _`host configuration interface`: ../Api/Core/Nethgui_Core_HostConfigurationInterface.html
.. _bind:
.. _validate:
.. _process:
.. _Nethgui_Core_ModuleInterface: ../Api/Core/Nethgui_Core_ModuleInterface.html
.. _getAdapter:
.. _Nethgui_Core_Module_Standard: ../Api/Core/Module/Nethgui_Core_Module_Standard.html
.. _Nethgui_Core_Module_Composite: ../Api/Core/Module/Nethgui_Core_Module_Composite.html



Module composition
------------------

A module can be composed of other modules. In this case the first
plays the *parent* role while the seconds play the *children* role.

The Nethgui framework has two concrete types of composition: the List
and the Controller.  The concept of *Composite* module is outlined in
the `Composite abstract class`_.

.. warning:: In a composite module, the parent module is fully
             responsible of its children: creation, initialization and
             all the operations defined by the framework must be
             propagated from the parent to its children.  Core module
             classes perform most of this work automatically, but you
             must do it by hand in your own classes.  Keep it in mind.

.. _`Composite abstract class`: ../Api/Core/Module/Nethgui_Core_Module_Composite.html



List composition
^^^^^^^^^^^^^^^^

In the List composition implementation the parent module forwards the
messages it receives to **all** its children.

Refer to the docblocks of `Nethgui_Core_Module_List`_ for the details
of each operation implementation.

The helper method ``loadChildren()`` instantiates a list of classes
adding each one as a child of the parent module.

.. note:: The List composition also implements two important 
          *user interface interactions*: the **tabs** and the **form**.  
          See the List class constructor docblock for details.

.. _`Nethgui_Core_Module_List`: ../Api/Core/Module/Nethgui_Core_Module_List.html



Controller composition
^^^^^^^^^^^^^^^^^^^^^^

In the Controller composition implementation the parent module (the
controller) forwards the messages it receives to the **current** child
(the action).

The current action is identified into the ``request`` object passed to
the parent as the argument to the ``bind()`` operation.

To find the current action identifier these rules apply:

1. Read the first request *argument* (this corresponds to the first
   URL path segment after the module identifier);

2. If the first argument is missing and the request has been submitted
   by the User, consider the builtin ``__action`` request parameter;

3. Otherwise the current action is undefined and the controller does
   nothing.

A more concrete Controller composition is discussed in `The Table
Controller`_ section.




View layer
==========

The View layer displays the module parameters data on the User's
screen according to a set of customizable Templates and pre-defined
user interactions. As stated before, you do not have to care about the
interface "look and feeling": the Nethgui framework provides a set of
ready-to-use controls that you employ to build the user interface.

The rendering phase, as stated in `Module dissection`_, is
accomplished in two steps.

1. transferring data into the view objects;

2. serialize the view objects into an XHTML or JSON string.

**Step 1**. After the ``process()`` operation a Module receives a
View object as first argument to prepareView_ method::

   public function prepareView(Nethgui_Core_ViewInterface $view, $mode) 
   {
       parent::prepareView($view, $mode);
   }

Basic implementation automatically transfers all the module Parameters
into the view object.

.. tip:: A View object resembles a PHP array, where you can store
         data using keys and values; indeed a View implements
         ArrayAccess_ and IteratorAggregate_ interfaces.

The ``mode`` parameter tells if we are performing a full view refresh
or a partial update.  The first case corresponds to the generation of
the XHTML document, that requires, for instance, all the possible
OPTIONs elements of a SELECT tag.  The second case is typically
associated to the generation of a JSON response, where only the actual
parameters value must be transferred to the client: in the case of the
SELECT tag we can transfer a ``value``-holding attribute only.

**Step 2**. The view object is transformed into a string, calling a
`Template`_ script or callback method.  In both situations you
can call any method defined by the abstract Renderer class to generate
the right XHTML code for each control.

.. _ArrayAccess: http://php.net/manual/en/class.arrayaccess.php
.. _IteratorAggregate: http://php.net/manual/en/class.iteratoraggregate.php
.. _prepareView: ../Api/Core/Module/Nethgui_Core_Module_Standard.html#prepareView



Template
--------


**A Template script** is a common PHP script.  Any string printed from
it, or any unescaped HTML fragment will take part in the module string
output.

A Template script has a ``.php`` file name extension, while the file
name is expected to be a slightly modified version of the associated
module class name, where the ``_Module_`` substring is replaced with
``_Template_``. Thus, if the module class is ``User_Module_Example``,
defined in ``User/Module/Example.php`` the associated template script
would be guessed into ``User/Template/Example.php``.

.. tip:: You can explicitly declare the template associated with a
         View object calling the ``setTemplate()`` method. See the
         example below.

::

   class User_Module_MyModule extends Nethgui_Core_Module_Standard 
   {

     .
     .
     .

     public function prepareView(Nethgui_Core_ViewInterface $view, $mode) 
     {
         parent::prepareView($view, $mode);
  
         // Use User/Template/MyAlternativeTemplate.php
         // instead of defalt User/Template/MyModule.php
         $view->setTemplate("User_Template_MyAlternativeTemplate");
     }

     .
     .
     .

A Template script receives a local variable: ``$view``. It is bound to
a Renderer object, and you can use it to retrieve the View state and
generate the control output. Supposing we have a `DomainName`
parameter in the view state, in
``User/Template/MyAlternativeTemplate.php`` we can write::

  <p>Domain: <?php echo $view['DomainName'] ?></p>

     
**A Template callback method** is a PHP callable function that returns
a string, representing the Template output. We can call the
``setTemplate()`` method with a PHP callable as argument, instead of a
string, as we have seen in the Template script case. In this way, the
callable function is invoked instead of the script::

   class User_Module_MyModule extends Nethgui_Core_Module_Standard 
   {

     .
     .
     .

     public function prepareView(Nethgui_Core_ViewInterface $view, $mode) 
     {
         parent::prepareView($view, $mode);
  
         // Use User/Template/MyAlternativeTemplate.php
         // instead of defalt User/Template/MyModule.php
         $view->setTemplate(array($this, "renderMyModule"));
     }

     // The callback function must be declared "public".
     public function renderMyModule(Nethgui_Renderer_Abstract $view) 
     {
        return "<p>Domain: " . $view['DomainName'] . "</p>";
     }

     .
     .
     .


Renderer
--------

You may have noticed in the `Template`_ section that a Template, both
script and callback method, receives a variable: ``$view``. 

That variable holds a `Nethgui_Renderer_Abstract`_ object, a
"decorated" View object that forbids any change to the view state and
provides a set of helper methods to draw the user interface.

For instance to put an input field bound to a ``ipAddress`` view
value you can write::

    /* PHP Template script */ 
    echo $view->textInput('ipAddress');

This produces the following XHTML code::

    <!-- XHTML code -->
    <div class="labeled-control label-above">
         <label for="MyModule_ipAddress">Indirizzo di rete</label>
         <input type="text" 
                id="MyModule_ipAddress" 
                name="MyModule[ipAddress]" 
                class="TextInput MyModule_ipAddress" 
                value="" />
    </div>

Method exist to draw any control or controls container as described in
[UI-CONTROLS]_ and [UI-INTERACTIONS]_.

The ``textInput()``, as other Renderer methods, returns an object
implementing `Nethgui_Renderer_WidgetInterface`_ (a *Widget*). Widgets
can be nested in a hierarchical way through the ``insert()``
method. Of course ``insert()`` makes sense only on *container*
widgets.

::

    /* PHP Template script */
    echo $view->panel()
         ->insert($view->textInput('ipAddress'))
         ->insert($view->textInput('ipMask'));

The previous fragment generates a *panel* (an XHTML DIV tag)
containing two input fields.  Note that ``insert()`` as other methods
of the `Nethgui_Renderer_WidgetInterface`_ return the same object,
allowing `method chaining`_.


.. [UI-CONTROLS] `Basic UI Controls <../UserInterface/BasicUiControls.html>`_ *Nethgui User Interface Design* 
.. [UI-INTERACTIONS] `Interactions <../UserInterface/Interactions.html>`__ *Nethgui User Interface Design* 
.. _`Nethgui_Renderer_Abstract`: ../Api/Renderer/Nethgui_Renderer_Abstract.html
.. _`Nethgui_Renderer_WidgetInterface`: ../Api/Renderer/Nethgui_Renderer_WidgetInterface.html
.. _`method chaining`: http://en.wikipedia.org/wiki/Method_chaining

Implementing a simple Module
============================

In this section we will write a simple Module that controls the
enabled/disabled state of an hypothetical *OnOffService* in project
*GearUi*.

The state of the service is defined in the Host Configuration
Database, by the value of ``status`` property in key ``onoff`` of
``myconf`` database. So we initialize the required prop to
``disabled`` with the following shell command::

  # /sbin/e-smith/db myconf set onoff service status disabled

To implement a Module you should extend
`Nethgui_Core_Module_Standard`_ class. So we create a new PHP file
under ``GearUi/Module/`` subdirectory: ``OnOffService.php``.

In ``OnOffService.php`` we write::

   <?php

   class GearUi_Module_OnOffService extends Nethgui_Core_Module_Standard 
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
  ``Nethgui_Core_Module_Standard``, as the file path is given
  implicitly in the class name, substituting underscores ``_`` with
  slashes ``/``.

* We re-implement ``initialize()`` method to declare a Module
  parameter so we *must* call parent's initialize_.

In ``initialize()`` body we declare a parameter, calling
declareParameter_:
  
- the parameter name is ``serviceStatus``;
    
- the parameter value must match ``/^(enabled|disabled)$/`` `regular
  expression`_ to be considered valid;
    
- the parameter value, if valid, is written to prop ``status`` of key
  ``onoff`` in ``myconf`` database.

The OnOffModule class is now fully functional, as the basic class
implementation provides transferring the parameter to/from database
value, if it is correctly validated.

Moreover the basic class transfers the parameter value to the `View
layer`_, so that we can put it in HTML format through a Template.

Of course, we have to write the Template first, so we create another
PHP file, this time under ``GearUi/Template/`` directory,
``OnOffService.php``::

   <!-- GearUi/Template/OnOffService.php contents --><?php 
   echo $view
          ->insert($view->checkBox('serviceStatus', 'enabled'))
          ->insert(checkBox('serviceStatus', 'disabled'))
   ;
   ?>
   

.. _Test_Tool_ModuleTestCase: 
.. _basic testing class: http://nethgui.nethesis.it/docs/Tests/Tool/ModuleTestCase.html
.. _Nethgui_Core_Module_Standard: ../Api/Core/Module/Nethgui_Core_Module_Standard.html
.. _Nethgui_Core_Module_Composite: ../Api/Core/Module/Nethgui_Core_Module_Composite.html
.. _initialize: ../Api/Core/Module/Nethgui_Core_Module_Standard.html#initialize
.. _declareParameter: ../Api/Core/Module/Nethgui_Core_Module_Standard.html#declareParameter
.. _regular expression: http://php.net/manual/en/function.preg-match.php


Module Testing
==============

In the `previous section example`_ we
must test OnOffService in three scenarios:

1. The User turns the service ON.

2. The User turns the service OFF.

3. The User takes no action.

We can check if OnOffService module is correct by writing a
PHPUnit_ test case. Nethgui comes with a basic class to be extended to
build module tests upon it: Test_Tool_ModuleTestCase_.

As we are testing a module of the hypothetical *GearUi* project , we
put our test case class under ``Tests/Unit/GearUi/Module/`` directory;
the class file name must be ending with ``Test.php``.

In ``OnOffServiceTest.php`` we write::

   <?php

   class GearUi_Module_OnOffServiceTest extends ModuleTestCase 
   {
       protected function setUp() 
       {
           $this->object = new GearUi_Module_OnOffService();
       }

       public function testTurnOn() 
       {
           $env = new Test_Tool_ModuleTestEnvironment();

           // 1. Set the input parameter value:
           $env->setRequest(array('serviceStatus'=>'enabled'));

           // 2. Expect "serviceStatus" has value "enabled" in view state:
           $env->setView(array('serviceStatus', 'enabled'));

           // 3. Create a mock object to simulate the real database object           
           $myConfDb0 = new Test_Tool_MockState();

           // 3.1 Return "disabled" on getProp('onoff', 'status'):
           $myConfDb0->set(Test_Tool_DB::getProp('onoff', 'status'), 'disabled');

           // 3.2 Enter a new state on setProp():
           $myConfDb1 = $myConfDb0->transition(Test_Tool_DB::setProp('onoff', 'status', 'enabled'), TRUE);

           // 3.3 Mark state as "final":
           $myConfDb1->setFinal();

           // 3.4 Set the initial state of `myconf` database:
           $env->setDatabase('myconf', $myConfDb0);

           $this->runModuleTest($this->object, $env);
       }
      
       public function testTurnOff() 
       {
           $this->markTestIncomplete();                      
       }

       public function testNoAction() 
       {
           $this->markTestIncomplete();                      
       }

   } // end of class

Consider the body of ``testTurnOn()`` method.  To run the test
procedure we have to create and set up a `test environment object`_.

* setRequest() defines the request object contents that will be passed to the module bind() method. 

* setView() defines the expected view parameters value after the module prepareView() method.

* setDatabase() defines the states of a specific database: each read
  and write operation must be properly defined. See
  `Test_Tool_MockState`_ for details.

.. _`Test_Tool_MockState`: ../Api
.. _`test environment object`: ../Api
.. _PHPUnit: http://www.phpunit.de/manual/3.5/en/index.html
.. _`previous section example`: `Implementing a simple Module`_

Localization
============

TODO;

The Table Controller
====================

TODO; Implement a CRUD scenario with TableController.
