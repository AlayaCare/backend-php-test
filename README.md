Alayacare PHP backend skill test
==========================

### My notes
* I added a message system to notify user about the success / fail of a request
* I updated the app to use real http actions instead of strange endpoints. This way the API is REST compliant 😙
* ... but instead of use ugly notation `<input type="hidden" id="_method" name="_method" value="PUT" />` using a frontend lib will be better. As it's a backend test I limit at maximum the javascript.
* I fix some security issue (escaping user inputs, checking task owner) but the biggest fail is storing user password in clear in database instead of hashes !!!
* I choose to use a `DateTime` instead of a simple `Boolean` for **TASK-2**. With this, we can keep a trace of the user done date. It's bigger than a `Boolean` in DB but more convenient for the user. I choose this way for this test.
* We can improve the API by caching task retrieval and update cache on `POST` / `PUT` / `DELETE`
* For `TASK-4`, I used `FlashBags` as requested but doing it on frontend is the right way to handle a confirmation.
* Sorry for Task 6, but it will take too much time to me to lear how to do it on a framework I'll never use. Plus it's Sunday ☀️ 😎 !

### Application
The TODO App allows a user to add reminders of thing he needs to do. Here are the requirement for the app.
* Users can add, delete and see their todos.
* All the todos are private, users can't see other user's todos.
* Users must be logged in order to add/delete/see their todos.

Credentials:
* username: **user1**
* password: **user1**

#### Homepage:
![Homepage](/web/img/homepage.png?raw=true "Homepage")

#### Login page:
![Login page](/web/img/login-page.png?raw=true "Login page")

#### Todos:
![Todos](/web/img/todos.png?raw=true "Todos")

### Requirements
* php 5.3+
* mysql
* A github account

### Installation
**/!\ You need to fork this repository. See [How to submit your work?](#how-to-submit-your-work)**
```sh
php composer.phar install
cp config/config.yml.dist config/config.yml
mysql -u root <database> < resources/database.sql
mysql -u root <database> < resources/fixtures.sql
mysql -u root <database> < resources/adding_todos_done_date.sql
php -S localhost:1337 -t web/ web/index.php
```
You can change the database connection from the file `config/config.yml`.

### Instructions

You will be asked to improve the code of this app with the following tasks.

You can complete the tasks in any order.

Separate your commits by task and use the following format for your commit messages: TASK-{task number}: {meaningful message}

### Tasks
* TASK 1: As a user I can't add a todo without a description.
* TASK 2: As a user I can mark a todo as completed.
    - Write a database migration script in `resources/`
* TASK 3: As a user I can view a todo in a JSON format.
    - Ex: /todo/{id}/json => {id: 1, user_id: 1, description: "Lorem Ipsum"}
* TASK 4: As a user I can see a confirmation message when I add/delete a todo.
    - Hint: Use session FlashBag.
* TASK 5: As a user I can see my list of todos paginated.
* TASK 6: Implement an ORM database access layer so we don’t have SQL in the controller code.

Extra tasks:
- Fix any bug you may find.
- Fix any security issue you may find.

### Documentation
This app use [Silex](http://silex.sensiolabs.org/), a  micro-framework based on the Symfony2 Components.
Documentation can be found here: http://silex.sensiolabs.org/documentation


### How to submit your work?

1. ##### First you need to fork this repository.
![Forking a repo](/web/img/fork.png?raw=true "Forking a repo")

2. ##### Then clone your fork locally.
![Cloning a repo](/web/img/clone.png?raw=true "Cloning a repo")

3. ##### Install the app locally. See the [Installation Guide] (#Installation).

4. ##### Once you've completed your work, you can submit a pull-request to the remote repository.
![ a Pull Request](/web/img/pull-request.png?raw=true "Creating a Pull Request")

5. ##### Review your changes and validate.
![Validating a Pull Request](/web/img/pull-request-review.png?raw=true "Validating a Pull Request")



And you're done!


More documentation on Github:
* https://help.github.com/articles/fork-a-repo/
* https://help.github.com/articles/using-pull-requests/
