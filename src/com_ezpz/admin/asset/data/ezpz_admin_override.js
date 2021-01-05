var EzpzOverrideEndpoints = {
  "refresh_token":"/administrator/index.php?option=com_ezpz&view=api&endpoint=/api/v1/joomla/refresh/token",
  "expire_in":"/administrator/index.php?option=com_ezpz&view=api&endpoint=/api/v1/joomla/expire-in",
  "get_auth":"/administrator/index.php?option=com_ezpz&view=api&endpoint=/api/v1/joomla/authenticated-user",
  "loginPageRedirectUrl":"{loginPageRedirectUrl}",
  "installPageRedirectUrl":"/administrator/index.php?option=com_ezpz&view=install",
  "csrfToken": "/administrator/index.php?option=com_ezpz&view=api&endpoint=/api/v1/joomla/crsf-token",
  "scriptUrlRegex": /^(?:http|https):\/\/[^/]+(\/.*)\/(\/administrator\/templates\/).*\.js(\?.*)?$/
};
