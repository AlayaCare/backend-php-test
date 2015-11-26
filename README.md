Alayacare PHP backend skill test
==========================

### Requirements
* php 5.3+
* mysql

### Installation
```sh
php composer.phar install
cp config/config.yml.dist config/config.yml
mysql -u root <database> < resources/database.sql
mysql -u root <database> < resources/fixtures.sql
php -S localhost:1337 -t web/ web/index.php
```
You can change the database connection from the file `config/config.yml`.

### Application
The TODO App allow a user to add reminders of thing he needs to do. Here are the requirement for the app.
* The user can add, delete and see their todos.
* All the todos are private, a user can't see other user's todos.
* User must be logged in order to add/delete/see their todos.

### Instructions

You will be asked to improve the code of this app with the following tasks.

You can complete the tasks in any order.

The test should take about an hour.
Not all the tasks can be completed in that time.

Separate your commits by task and use the following format for your commit messages: TASK-{task number}: {meaningful message}

### Tasks
* TASK 1: As a user I can't add a todo without a description.
* TASK 2: As a user I can mark a todo as completed.
    - Write a database migration script in `resources/`
* TASK 3 As a user I can view a todo in a JSON format.
    - Ex: /todo/{id}/json => {id: 1, user_id: 1, description: "Lorem Ipsum"}
* TASK 4: As a user I can see a confirmation message when I add/delete a todo.
    - Hint: Use session FlashBag.
* TASK 5: As a user I can see my list of todos paginated.

Extra tasks:
- Fix any bug you may find.
- Fix the security issue you may find.

### Documentation
This app use [Silex](http://silex.sensiolabs.org/), a  micro-framework based on the Symfony2 Components.
Documentation can be found here: http://silex.sensiolabs.org/documentation
