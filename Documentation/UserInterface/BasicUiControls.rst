===================
 Basic UI Controls
===================
-------------------------------
 NethGui User Interface Design
-------------------------------

.. contents:: 
.. sectnum::

The idea is taken from `alexking.org`_

.. _`alexking.org`: http://alexking.org/dev/ASCII_UI_controls.txt

Controls
--------

Text input field (normal/focused)::

  [__________________] 

  [I_________________]

Radio button::

  (o) Selected
  ( ) Unselected

Checkboxes::

  [x] Selected
  [ ] Unselected

Dropdown menu::

  [__________|v] 

Buttons (enabled & disabled)::

  [ Cancel ]  | Apply |  



Containers
----------

Tabs::

   .-----------. .-----------.
   |   Tab 1   | |   Tab 2   |
  -+           +-+-----------+-----------...


Dialog::

        +----------------------------------+
        | Title                          X |
        +----------------------------------+
        |                                  |
        | Are you sure?                    |
        |                                  |
        |                  [ Yes ] [ No  ] |
        +----------------------------------+


Fieldset with/without label::

   .Fieldset label ------------
   |
   | ( ) Disabled
   | (o) Enabled
   |  

   .--------------------------
   |
   | ( ) Disabled
   | (o) Enabled
   |  

