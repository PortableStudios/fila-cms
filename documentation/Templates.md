# Templates

You can override any FilaCms namespaced template by creating a blade file in your `resources/views/fila-cms` folder, for the following paths:

**layouts/app.blade.php**
The main layout file.  All pages within the Front End application derive from this.  You must, at a minimum, include Livewire styles and scripts, in this file, for the application to function.

**layouts/document-head.blade.php**
Everything between `<head>` and `</head>` in the layout file comes from here.

**layouts/page-head.blade.php**
Everything above the main page content is rendered from this file

**layouts/page-footer.blade.php**
Everything below the main page content is rendered from this file.

**layouts/email.blade.php**
This is the main email rendering template that providers the wrapper and styling for emails.

**components/auth/login-form.blade.php**
The login form itself

**components/auth/user-profile-form.blade.php**
The user profile form

**auth/reset-password.blade.php**
The "Reset Password" page

**auth/login.blade.php**
The "Login page" page

**auth/user-profile.blade.php**
The "User Profile" page

**notifications/form-submitted.blade.php**
This renders the contents of the form submittion notification email.