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


Text input (normal/focused)::

  [__________________] 

  [I_________________]

Text area NG556_::

  |____________________| 
  |____________________| 
  |____________________| 
  |____________________|
  |____________________|     


Radio button::

  (o) Selected
  ( ) Unselected

Checkboxes::

  [x] Selected
  [ ] Unselected

Dropdown menu NG348_::

  [__________|v] 

Buttons (enabled & disabled)::

  [ Cancel ]  | Apply |  

Button list NG476_::

  [ Button 1 ]  [ Button 2 ]  [ Button 3 ]

Button drop-down panel (normal) NG528_::

  [ Button v ]

Button drop-down panel (clicked) NG528_::

  [ Button v ]
  +--------------------+
  |                    |
  | // panel contents  |
  |                    |
  +--------------------+

.. note:: Panel contents can be buttons, links or any other widget.

Button set NG528_::

  [ Button 1 | Button 2 | Button 3 ]

Button set with upper limit (set to 1) NG528_::

  [ Main Action | v ]

Button set with upper limit (``v`` is clicked) NG528_::

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

Progress bar NG554_::

  [ ########## 50% __________ ]


Slider NG1242_::

  [ ----O-------------------- ] Value label

.. _NG554: http://dev.nethesis.it/issues/554
.. _NG476: http://dev.nethesis.it/issues/476
.. _NG528: http://dev.nethesis.it/issues/528
.. _NG348: http://dev.nethesis.it/issues/348
.. _NG556: http://dev.nethesis.it/issues/556
.. _NG1242: http://dev.nethesis.it/issues/1242

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

