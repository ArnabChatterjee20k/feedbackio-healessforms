For learning php and php-utopia eco system and using it for my work at appwrite
An extension for my previous project(https://github.com/ArnabChatterjee20k/FeedbackIo)
I am having a plan to integrate form service to it. So why not writing that with php only

# Core features
* Admins can come and create forms space
* Admins will get the api endpoint
* Admins can paste the form endpoint in the html forms
* User can come and share their response
* Rate limiting and recaptcha
* Submission of result to the feedbackio backend analytics platform
* discord notification
* deployment

# Autocompletion
```bash
docker run --rm --interactive --tty   --volume $PWD:/app   composer update --ignore-platform-reqs --optimize-autoloader --no-plugins --no-scripts --prefer-dist
```