==============
 Interactions
==============
-------------------------------
 NethGui User Interface Design
-------------------------------

.. contents:: 
.. sectnum::

Apply changes (Save)
--------------------

The User wants to modify a form and save the changes.

1. The *Save* button is disabled

2. User changes some values on the form displayed.

3. The *Save* button becomes enabled.

4. User clicks on the *Save* button at the bottom of the form.

5. A *feedback message* appears on the top of the page to confirm
   everything is fine (or that some error has occurred).

   a) If everything is fine, *Save* button becomes disabled.
   b) Else the error message explains the problem and *Save* keeps enabled state.

After the last step::

   +----------------------------------------+
   |                                       X|
   | Congratulations! - feedback message    |
   |                                        |
   +----------------------------------------+

   ( ) Unselected  
   (o) Selected
      .----------------------------------.
      |                                  |
      |       Insert text [Hello_____]   |
      | Another insertion [World!____]   |
      |                                  |
      |  [x] Option 1                    |
      |  [x] Option 2                    |
      |  [ ] Option 3                    |
      +----------------------------------+

                                     | Save |



Choose and fill
---------------

1. A set of exclusive choices represented by radio buttons is displayed.

2. The User chooses, by clicking on a radio button or on its textual label.

3. A fieldset appears.

Initial state::

  (o) Unselected  
  ( ) Selected


Since the User has clicked on "Selected" a fieldset appears::

  ( ) Unselected  
  (o) Selected
     .--------------------
     |
     |       Insert text [__________]
     | Another insertion [__________]
     |
     |  [ ] Option 1
     |  [ ] Option 2
     |  [ ] Option 3
     |





Table CRUD
----------

Sample table::

   +--------------+----------------+---------------+-----------------------+
   | User name  v | First Name     | Last Name     | Actions               |
   +--------------+----------------+---------------+-----------------------+
   | johns        | John           | Smith         | [ Modify ] [ Delete ] |
   +--------------+----------------+---------------+-----------------------+
   | scott        | Scott          | Tiger         | [ Modify ] [ Delete ] |
   +--------------+----------------+---------------+-----------------------+
   
                                                         [ Create new User ]


Read
^^^^

Data is displayed in tabular form. Each row of the table ends with two
buttons:

1. Modify

2. Delete

The first row of the table contains the column headers. A click on the
header *may* change the order of the rows - this depends on the table
desired behaviour.


Create
^^^^^^

The User wants to create a new table element.

1. The User clicks on the *Create new...* button.

2. A form appears with necessary fields [#form-appears]_.

3. The User compiles the form and confirms (i.e. clicks a *Create*
   button).

4. If input is validated, a successful feedback is displayed on the
   top of the screen.

5. The original table is refreshed. Depending on sorting and
   pagination, the created row can be immediatly visible or not.  

   a) If the record is visible its temporarly highlighted.
   b) If the record is not visibile, the feedback message offers a
      shortcut to its position.




Update
^^^^^^

The User wants to change an existing table element.

1. The User clicks on the *Modify* button of the element row.

2. Create_ scenario applies, only form fields are pre-compiled with
   actual record values.


Delete
^^^^^^

The User wants to delete a table element

1. The User clicks on the *Delete* button of the element row.

2. A modal dialog asks for confirmation.

3. The User confirm deletion.

4. Successful feedback is displayed.

5. The deleted row folds up.

5. The original table is refreshed, coherently with its current
   sorting and pagination state.


.. [#form-appears] (1) A modal dialog box containing form fields
         appears, overlaying the screen or (2) The table is hidden and
         the form fields appear in its place.



Wizard
------

A wizard guides the User through a stepped procedure. Every step is a
form. For each step:

1. The User fills the form.

2. The Users clicks on the *Next* button .

3. The form is validated.

   a) In case of validation error the standard validation error
   procedure and visual feedback applies.

4. The next step form is displayed

On the last step the *Save* button is displayed instead of *Next*. 


Notes:

* At every step except the first a *Previous* button allows the User
  to switch back to previous step.

* Field values are remembered while the User moves forward and backward.


Wizard at intermediate step:: 

    1.  Account type
    2. *Personal informations*
    3.  Password settings
    4.  Confirmation

    First Name [___________________]
     Last Name [___________________]
       Country [___________|v]

                            [ Previous ] [   Next   ]

A brief summary of all the wizard steps is displayed on the top of
each form, emphasizing the current step.
