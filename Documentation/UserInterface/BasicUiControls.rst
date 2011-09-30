===================
 Basic UI Controls
===================
-------------------------------
 Nethgui User Interface Design
-------------------------------

.. contents:: 
.. sectnum::

The idea is taken from `alexking.org`_

.. _`alexking.org`: http://alexking.org/dev/ASCII_UI_controls.txt

Controls
--------

Text label::

  This is a paragraph.

  This is another paragraph.  Your name is "E. Smith".


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

Button list::

  [ Button 1 ]  [ Button 2 ]  [ Button 3 ]

Button drop-down panel (normal)::

  [ Button v ]

Button drop-down panel (clicked)::

  [ Button v ]
  +--------------------+
  |                    |
  | // panel contents  |
  |                    |
  +--------------------+

.. note:: Panel contents can be buttons, links or any other widget.

Button set::

  [ Button 1 | Button 2 | Button 3 ]

Button set with upper limit (set to 1)::

  [ Main Action | v ]

Button set with upper limit (``v`` is clicked)::

  [ Main Action | v ]
  +--------------------+
  | Action 2           |
  | Action 3           |
  | Action 4           |
  +--------------------+

Basic Selector (multiple)::

  [x] Label1 
  [ ] Label2
  [x] Label3 
  [x] Label4 
  [ ] Label5

Basic Selector (single)::

  ( ) Label1
  ( ) Label2
  (o) Label3



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

