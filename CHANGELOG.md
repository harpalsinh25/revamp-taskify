# CHANGELOG

## 2026-06-01

### Fixed

* Fixed Date of Joining (doj) validation error when custom dynamic date formats (such as `'d M, Y'`) are set system-wide. Removed the built-in Laravel `'date'` and `'after:dob'` validator rules which are incompatible with custom date formats, and implemented full dynamic format and chronological ordering validation using custom closures.

### Impacted Modules

* User Management (Backend)
* Client Management (Backend)

### Notes

* No migration required.

## 2026-06-01

### Fixed

* Fixed client dashboard statistics showing zero projects and tasks. Appended check to verify if the authenticated user is a client and avoid incorrectly overriding `$userIds` with the client ID, as well as filtering todos properly by client creator type.

### Impacted Modules

* Dashboard (Backend)

### Notes

* No migration required.
