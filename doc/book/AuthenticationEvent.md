# The AuthenticationEvent

simpleinvoices-authentication defines and utilizes a custom `Zend\EventManager\Event` implementation,
`Simpleinvoices\Authentication\AuthenticationEvent`. This event is created during
`SimpleInvoices\Authentication\AuthenticationService::authenticate()`
and is passed when triggering all authentication events.

The `AuthenticationEvent` adds accessors and mutators for the following:

- `Adapter` object.
- `AuthenticationService` object.

The methods it defines are:

- `setAdapter($adapter)`
- `getAdapter()`
- `setAuthenticationService($authenticationService)`
- `getAuthenticationService()`

The `AuthenticationService`, and `Adapter` are injected during the
`authenticate` method call. 

## Order of events

The following events are triggered, in the following order:

Name                          | Constant                                          | Description
------------------------------|---------------------------------------------------|------------
`siAuth.authenticate`         | `AuthenticationEvent::EVENT_AUTHENTICATE`         | Event triggered when 'SimpleInvoices\Authentication\AuthenticationService::authenticate()` has been called.
`siAuth.authenticate.success` | `AuthenticationEvent::EVENT_AUTHENTICATE_SUCCESS` | Event triggered in case of a valid authentication attempt.
`siAuth.authenticate.error`   | `AuthenticationEvent::EVENT_AUTHENTICATE_ERROR`   | Event triggered in case of an invalid authentication attempt.
`siAuth.authenticate.sql`     | `AuthenticationEvent::EVENT_AUTHENTICATE_SQL`     | Event triggeres when the select query has been built.

The following sections provide more detail on each event.

## `AuthenticationEvent::EVENT_AUTHENTICATE` ("siAuth.authenticate")

### Triggered By

This event is triggered by the following classes:

Class                                                 | In Method
------------------------------------------------------|----------------
`SimpleInvoices\Authentication\AuthenticationService` | `authenticate`

## `AuthenticationEvent::EVENT_AUTHENTICATE_SUCCESS` ("siAuth.authenticate.success")

### Triggered By

This event is triggered by the following classes:

Class                                                 | In Method
------------------------------------------------------|----------------
`SimpleInvoices\Authentication\AuthenticationService` | `authenticate`

### Extra Parameters Set

This events includes the authentication result (`Zend\Authentication\Results`)

Parameter             | Description
----------------------|------------------------------------------
`authenticate_result` | Instance of `Zend\Authentication\Result`

## `AuthenticationEvent::EVENT_AUTHENTICATE_ERROR` ("siAuth.authenticate.error")

### Triggered By

This event is triggered by the following classes:

Class                                                 | In Method
------------------------------------------------------|----------------
`SimpleInvoices\Authentication\AuthenticationService` | `authenticate`

### Extra Parameters Set

This events includes the authentication result (`Zend\Authentication\Results`)

Parameter             | Description
----------------------|------------------------------------------
`authenticate_result` | Instance of `Zend\Authentication\Result`


## `AuthenticationEvent::EVENT_AUTHENTICATE_SQL` ("siAuth.authenticate.sql")

### Triggered By

This event is triggered by the following classes:

Class                                           | In Method
------------------------------------------------|----------------
`SimpleInvoices\Authentication\Adapter\DbTable` | `authenticate`

### Extra Parameters Set

This events includes the Select statement (`Zend\Db\Sql\Select`)

Parameter | Description
----------|------------------------------------------
`select`  | Instance of `Zend\Db\Sql\Select` containing the Select statement for authentication.