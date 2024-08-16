<?php

// require_once("languages/en_us/Settings/Workflow2.php");

$languageStrings = [
    // Global
    'LBL_CHOOSE' => '::: Выбрать :::',
    'LBL_NEW_VERSION_AVAILABLE' => 'Версия %s доступна. Пожалуйста, обновитесь!',
    'LBL_BACK_TO_ADMIN' => 'назад в панель управления',
    'LBL_IMPORT_SUCCESS' => 'Импортирование успешно завершено',
    'LBL_SELECT_FILE' => 'выбрать файл',
    'LBL_IMPORT_PASSWORD' => 'пароль к файлу',
    'new workflow name' => 'название нового бизнес-процесса',
    'start import' => 'начать импорт',

    // Главная панель управления
    'Create new workflow' => 'Создать новый бизнес-процесс',
    'LBL_UPDATE_MODULE' => 'Обновить модуль',
    'Activate' => 'Включить',
    'Deactivate' => 'Выключить',
    'Edit' => 'Изменить',
    'Statistics' => 'Статистика',
    'delete' => 'Удалить',
    'Export' => 'Экспорт',
    'You could set a password to protect the export file.' => 'Вы можете установить пароль, чтобы защитить экспортируемые файлы.',
    'import Workflow' => 'Импортировать бизнес-процесс',

    // Настройки бизнес-процесса
    'Settings' => 'Настройки',
    'Back' => 'Назад',
    'Main module' => 'Главный модуль',
    'Title' => 'Название',
    'Save Settings' => 'Сохранить настройки',
    'Please choose a Main module!' => 'Пожалуйста, выберите главный модуль!',

    // Модуль
    'E-Mail senden' => 'Отправить письмо',
    'Warten' => 'Задержка',
    'Bedingung' => 'Условие',
    'Feld setzen' => 'Изменить поля',
    'LBL_TASK_STOP' => 'Остановить',
    'Besitzt Record passende Relation' => 'Существует ли связанная запись?',
    'Starte Workflow' => 'Запустить внешний бизнес-процесс',
    'Check MySQL query' => 'Выполнить запрос к MySQL',

    // Категории
    'flow' => 'Управление процессом',
    'communication' => 'Оповещение',
    'management' => 'Управление записями',
    'debug' => 'Отладка',
    'CRM Objects' => 'Пользователь CRM',

    'Weiter' => 'Далее',
    'Wahr' => 'Истина',
    'Falsch' => 'Ложь',
    'wahr' => 'Истина',
    'falsch' => 'Ложь',

    'Start' => 'Начало',

    // block settings
    'LBL_ACTIVE' => 'Активный',
    'LBL_INACTIVE' => 'Неактивный',

    'LBL_STATUS' => 'Состояние',
    'LBL_AUFGABENBEZEICHNUNG' => 'Название блока',
    'LBL_ZUSAMMENFASSUNG' => 'Описание',
    'LBL_AUSGABENBEZEICHNUNG' => 'Настройки блока',

    'LBL_SAVE' => 'Сохранить',
    'LBL_CANCEL' => 'Отмена',
    'LBL_DUPLICATE_BLOCK' => 'Дублировать блок',
    'LBL_DELETE_BLOCK' => 'Удалить блок',

    // отправка письма
    'Absender der eMail' => 'Данные отправителя',
    'BCC Empfänger' => 'Скрытая копия',

    'LBL_EMAIL_RECIPIENT' => 'Получатель',
    'LBL_EMAIL_CC' => 'Копия',
    'LBL_EMAIL_BCC' => 'Скрытая копия',
    'LBL_EMAIL_SUBJECT' => 'Тема',
    'LBL_SELECT_OPTION_DOTDOTDOT' => 'Выбрать...',
    'LBL_LOADING' => 'Загрузка...',
    'LBL_NO_TEMPLATES' => 'Нет шаблонов',
    'LBL_SELECT' => 'Выбрать',
    'LBL_MESSAGE' => 'Сообщение',

    'LBL_SENDER_NAME' => 'Имя отправителя',
    'LBL_SENDER_MAIL' => 'E-mail отправится',

    // Задержка
    'LBL_DATE_BASE' => 'На основе даты',
    'LBL_WAIT_MIN' => 'Минимальная задержка',
    'LBL_WAIT_UNTIL_NEXT' => 'Ждать, пока не...',
    'LBL_WAIT_UNTIL_TIME' => 'Ждать до...',

    'LBL_CURRENT_TIME' => 'Текущее время',
    'LBL_MINUTES' => 'Минут',
    'LBL_HOURS' => 'Часов',
    'LBL_DAYS' => 'Дней',
    'LBL_WEEKS' => 'Недель',

    'LBL_MONDAY' => 'Понедельник',
    'LBL_TUESDAY' => 'Вторник',
    'LBL_WEDNESDAY' => 'Среда',
    'LBL_THURSDAY' => 'Четверг',
    'LBL_FRIDAY' => 'Пятница',
    'LBL_SATURDAY' => 'Суббота',
    'LBL_SUNDAY' => 'Воскресенье',
    'LBL_USED_TZ' => 'Часовой пояс',

    // Mysql-проверка
    'CHECK_THIS_QUERY' => 'Выполнить этот запрос',
    'SUCCESS_IF_EQUAL_ROWS' => 'Посчитать строки',

    'LBL_ADD_GROUP' => 'Добавить группу условий',
    'LBL_REMOVE_GROUP' => 'Удалить группу условий',
    'LBL_ADD_CONDITION' => 'Добавить условие',
    'LBL_AND' => 'и',
    'LBL_OR' => 'или',
    'LBL_NOT' => 'не',

    'LBL_COND_EQUAL' => 'равно',
    'LBL_COND_CONTAINS' => 'содержит',
    'LBL_COND_BIGGER' => 'больше, чем',
    'LBL_COND_LOWER' => 'меньше, чем',
    'LBL_COND_STARTS_WITH' => 'начинается с',
    'LBL_COND_ENDS_WITH' => 'кончается на',
    'LBL_COND_IS_EMPTY' => 'нет значения',

    // Added 1.5
    'LBL_RUNTIME_WORKFLOW' => 'Бизнес-процесс запущен runtime of this workflow',
    'LBL_SYNCHRONOUS' => 'Немедленно',
    'LBL_ASYNCHRONOUS' => 'Отложено',
    'LBL_START_CONDITION' => 'Условие запуска',
    'LBL_PARALLEL_ALLOWED' => 'Параллельное выполнение',
    'LBL_USER_EXECUTE' => 'Запустить от имени пользователя',
    'LBL_START_CREATION' => '1. При первом сохранении',
    'LBL_START_EVERY' => '2. При каждом сохранении',
    'LBL_START_MANUELL' => '3. Вручную',
    'LBL_PARALLEL_NOT_ALLOW' => 'Запрещено',
    'LBL_PARALLEL_ALLOW' => 'Разрешено',
    'LBL_START_USER' => 'Пользователь по умолчанию (пользователь, запустивший бизнес-процесс)',

    // RelCheck
    'LBL_SEARCH_IN_MODULE' => 'Поиск по модулю',
    'LBL_FOUND_ROWS' => 'как минимум x записей',

    'global functions' => 'полезные функции',
    '- %s days' => '- %s дней',
    '+ %s days' => '+ %s дней',

    'LBL_COND_DATE_EMPTY' => 'дата не указана',

    // Add in 1.6
    'This Workflow administration needs IE9+, Google Chrome, Firefox or Safari!' => 'Этот бизнес-процесс требует браузеров IE9+, Google Chrome, Firefox or Safari!',
    'Do not open a Workflow with IE < 9!' => 'Не открывайте бизнес-процесс с помощью IE < 9!',
    'LBL_LICENSE_FOR' => 'Лицензия на',
    'by' => '',
    'LBL_ADD_FIELD' => 'добавить поле',
    'LOADING_INDICATOR' => 'Загрузка ...',
    'LBL_STATIC_VALUE' => 'Указать значение',
    'LBL_FIELD_VALUE' => 'Взять из поля',
    'LBL_FUNCTION_VALUE' => 'Функция',

    'This appears on every update or change of hostname and should disappear at next page view. ' => 'Это сообщение будет появляться при каждом обновлении или изменении имени хоста и будет исчезать при просмотре следующей страницы',
    'Failure during Reactivation.' => 'Восстановление не удалось',
    'You could not use the Workflow Designer Admin at the moment. Workflows are not stopped!' => 'Вы не можете использовать конструктор бизнес-процессов в данный момент! Бизнес-процессы не остановлены!',
    'Please make sure, the VtigerCRM could connect to the internet.' => 'Пожалуйста, убедитесь в том, что VtigerCRM имеет соединение с сетью Internet.',

    'LBL_WAIT_UNTIL_FUNCTION' => 'Ждать до указанного времени',
    'LBL_RETURN_UNIX_TIMESTAMP' => "Необходимо вернуть период в виде целое числа в формате <a href='http://ru.wikipedia.org/wiki/Unix_time' target='_blank'>Unix-Время</a>.",

    'LBL_CREATE_RECORD_OF_MODULE' => 'Создать запись выбранного модуля',

    'LBL_OK' => 'ОК',
    'LBL_REWORK' => 'Повторить',
    'LBL_DECLINE' => 'Отмена',

    'HEADLINE_WORKFLOW2_PERMISSION_PAGE' => 'Права на бизнес-процесс',
    'LBL_PERMISSION_TOP_HINT' => 'Следующие записи ждут, пока вы их одобрите:',
    'LBL_PERMISSION_BOTTOM_HINT' => 'Все бизнес-процессы на этой странице будут остановлены, пока вы не запустите их вручную. Вы можете согласиться или отказаться. Ваш ответ будет учитываться при определении маршрута выполнения бизнес-процесса. Обработка записи произойдет во время следующей автоматической проверке. (~10 минут)',
    'NO_ENTRY' => 'Нет доступных записей',
    'statistics_from' => 'С',
    'statistics_to' => 'По',

    'LBL_SHOW_VALUES' => 'Показать значения',
    'LBL_SHOW_REMOVED' => 'показать перемещенные соединения',
    'BTN_LOAD_STATS' => 'загрузить статистику',
    'CONNECTION_DETAILS' => 'Детали этого соединения',
    'close' => 'закрыть',
    'HEAD_TREND' => 'Линия выполнения',
    'HEAD_USAGE_OF_THIS_CONNECTION' => 'Использование соединения',
    'LBL_DATE' => 'Дата',

    'Create Record' => 'Создать Запись',
    'Create Task' => 'Создать Задачу',
    'create Event' => 'Создать Событие',
    'authorization Request' => 'Запрос разрешения',

    // Add in 1.61
    'Force execution of this workflow' => 'Запустить выполнение этого бизнес-процесса',
    'execute' => 'Выполнить',
    'activate sidebar Widget' => 'Включить виджет в боковой панели',
    'deactivate sidebar Widget' => 'Отключить виджет в боковой панели',

    // Add in 1.615
    'running Workflows with this record' => 'Запустить бизнес-процесс для этой записи',
    'will be continued' => 'Продолжение следует',

    // Add in 1.632
    'LBL_BACKGROUND_COLOR_ROW' => 'Цвет фона',
    'LBL_INFO_MESSAGE' => 'Информационное сообщение',

    // Add in 1.633
    'LBL_COND_IS_CHECKED' => 'Установлено',

    // Add in 1.643
    'LBL_RUN_WORKFLOW_WITH_NEW_RECORD' => 'Запустить бизнес-процесс для созданной записи',
    'LBL_NO_WORKFLOW' => 'Нет бизнес-процессов',

    // Add in 1.645
    'LBL_FOUND_ERROR' => 'Найдены ошибки',

    'SEND_DEBUG_REPORT' => 'Послать отчет об ошибке',
    'LBL_DEBUG_HEAD' => 'Если вы нашли ошибку в конструкторе бизнес-процессов, нужно помочь разработчикам и отправить эту форму. Эти данные будут отправлены разработчикам, чтобы помочь восстановить ход событий, приводящих к проблеме.<br><br>Эти данные будут отправлены по почте, будет использоваться тот же тип шифрования, что указан в настройках вашей CRM.<br><br>В этих полях можно увидеть информацию, которая будет отправлена.<br><strong>Если вы не хотите посылать какую-то информацию, пожалуйста, просто удалите значения из соответствующих полей.</strong>',
    'LBL_DEBUG_MIDDLE' => 'Структура базы данных конструктора бизнес-процессов будет в следующем поле',
    'LBL_DEBUG_BOTTOM' => 'Пожалуйста, опишите вашу проблему (на английском языке) или отправьте письмо по электронной почте, чтобы было легко понять что отчета об ошибке и это письма относятся к одной и той же ошибке',

    'create Comment' => 'Написать комментарий',
    'LBL_CREATE_COMMENT_TEXT' => 'Комментарий',
    'LBL_COMMENT_RECORD' => 'присвоить этой записи:',
    'LBL_THIS_RECORD' => 'сохраненная запись',

    'SELECT_WORKFLOW' => 'Какой бизнес-процесс выполнить?',
    'LBL_WORKFLOW2' => 'Конструктор бизнес-процессов',
    'LBL_ALLOW_PARALLEL_EXECUTION' => 'Если бизнес-процесс уже запущен и выполняется с какой-то записью, следует ли запускать его во второй раз?',
    'LBL_PROGRESS' => 'Прогресс',

    // Add in 1.65
    'LBL_SELECT_MAIL_TEMPLATE' => 'Шаблон письма',

    // Add in 1.7
    'LBL_ENVIRONMENTAL_VARS_HEAD' => 'Переменные окружения',
    'LBL_ENVIRONMENTAL_DESCRIPTION' => 'Если Вы не собираетесь использовать эти данные в другом месте, игнорируйте этот блок.',
    'LBL_EXPCOND_DESCRIPTION' => "Вы можете создать свое собственное условие, которое должно работать в PHP.<br><em>Единственное условие: вы должны вернуть 'yes' или 'no' с помощью инструкции <b>return</b</em><br><br><strong> Эта функция специально разработана для экспертов, и при неправильном использовании может изменить способ обработки записи и даже сломать вашу систему управления записями!</strong>",
    'Resend Mail' => 'Повторить отправку',
    'LBL_RESEND_MAIL_DESCRIPTION' => 'Diese Aufgabe wiederholt den Versand einer zuvor versendeten eMail.<br>Dabei besteht die Möglichkeit einen Text anzugeben, welcher vor dem ehemaligem Mailtext eingef&uuml;gt wird.',
    'LBL_CREATE_CONTACT' => 'Создать Контакт',
    'LBL_CREATE_ACCOUNT' => 'Создать Контрагент',
    'LBL_CREATE_POTENTIAL' => 'Создать Сделку',
    'LBL_REMOVE_RECORD' => 'Удалить запись',
    'LBL_HINT_TASK_REMOVE' => 'После удаления, вы не должны ничего делать с удаленной записью, поскольку это может привести к ошибкам!',
    'LBL_REDIRECT_AFTER_WORKFLOW' => 'Должен ли браузер перенаправить вас на эту запись,<br> после бизнес-процесса?',
    'LBL_GLOBAL_SEARCH' => 'Глобальный поиск',
    'LBL_SEARCH_EXEC_WORKFLOW' => 'Выполнить бизнес-процесс для другой записи',
    'LBL_SEARCH_EXEC_EXPRESSION' => 'Вычислить выражение для другой записи',
    'LBL_EXEC_FOR_THIS_NUM_ROWS' => 'Выполнить для х записей',
    'LBL_EMPTY_ALL_RECORDS' => 'Оставить пустым для всех результатов',
    'LBL_CAT_EXPERT' => 'Специальные инструменты',
    'LBL_CUSTOM_CONDITION' => 'Специальное условие',
    'LBL_START_MAIL_SEND' => '4. При отправке письма',
    'LBL_START_CREATE_COMMENT' => '5. При добавлении комментария',

    // Add in 1.705
    'LBL_SETTINGS_DB_CHECK' => 'Проверить базу данных',
    'LBL_SETTINGS_DB_CHECK_DESC' => 'Проверить таблицы',

    // Add in 1.707
    'LBL_COND_IS_NUMERIC' => 'Число',
    'LBL_COND_HAS_CHANGED' => 'Изменилось на',
    'LBL_WAIT_UNTIL_NEXT_MONTHDAY' => 'Ждать до ... ',
    'LBL_WAIT_UNTIL_NEXT_MONTHDAY2' => 'День ... ',
    'LBL_WAIT_UNTIL_NEXT_MONTHDAY_TITLE' => 'Пример: на 18й день этого или следующего месяца',

    'LBL_NEXT_WEEK' => 'Неделя',
    'LBL_NEXT_MONTH' => 'Месяц',

    // Add in 1.708
    'LBL_SETTINGS_REMOVE' => 'Удалить конструктор бизнес-процессов',
    'LBL_SETTINGS_REMOVE_DESC' => 'Удалить все файлы и таблицы конструктора бизнес-процессов',
    'LBL_MOD_REMOVE_WARN' => 'Если вы подтвердите удаление, то потеряете все бизнес-процессы в конструкторе бизнес-процессов.<br>Для подтверждения установите флажок и нажмите кнопку!',
    'LBL_MOD_REMOVE_BUTTON' => 'Да, удалить модуль!',

    'LOG_ERROR_FILE' => 'Записать лог в файл',
    'LOG_ERROR_EMAIL' => 'Отправить по почте',
    'LOG_ERROR_NONE' => 'Без логирования',
    'LBL_SETTINGS_LOGGING' => 'Управление логами',
    'LBL_SETTINGS_LOGGING_DESC' => 'Настройки логирования',
    'LBL_LOGS_ERROR' => 'Как бы вы хотели получать данные об ошибках?',
    'LBL_LOGS_ERROR_VALUE' => 'Сообщение об ошибке для eMail или имени файла:',
    'LBL_LOGS_ERROR_HEAD' => 'Сообщение об ошибке',

    'LBL_LOGS_HEAD' => 'Другие логи',
    'LBL_ALL_LOGS' => "Как бы вы хотели сохранять другие логи?<br><em><span style='font-size:10px;font-weight:bold;'>Ухудшит производительность!</span></em>",
    'LBL_ALL_LOGS_VALUE' => 'Имя файла для записи других логов',
    'LBL_ALL_LOGS_CLEAR' => 'Очистить все записи',
    'LBL_ALL_LOGS_TABLE_CLEAR' => 'Очистить таблицу для других логов',
    'LBL_DEBUG_LOG' => 'Лог',
    'LBL_LOGS_HEAD_ENTRIES' => 'Записывать данные',

    // Create INventory
    'LBL_ADD_PRODUCT' => 'Добавить Товар',
    'LBL_NO_PERSON' => 'Нет',
    'LBL_CURRENTLY_IN_DELAY' => 'Приостановлено',
    'LBL_WAITING_SINCE' => 'Ожидание с',
    'LBL_WAITING_UNTIL' => 'Ожидание до',

    'LBL_PARSE_STRING_TASK' => 'Регулярное выражение',
    'LBL_REGEX_VALUE' => 'Регулярное выражение',
    'LBL_REGEX_TARGET_ELEMENT' => 'Target Index',
    'LBL_REGEX_TARGET_ENV_VAR' => 'Записать в переменную окружения',
    'LBL_REGEX_TEST_STRING' => 'Протестировать на<br><br>Пожалуйста, сохранитесь прежде, чем делать это!',
    'LBL_RUN_TEST' => 'Начать тест',
    'LBL_TEST_RESULT' => 'Результаты теста',
    'LBL_BEFORE_TEST_SET_REGEX' => 'Регулярное выражение не определено!',
    'LBL_REGEX_SOURCE' => 'Регулярное выражение',

    'ordermanagement' => 'Управление заказами',
    'BTN_AUTH_MANAGEMENT' => 'Разрешения',

    'LBL_AUTH_SELECT_INHERIT' => 'Использовать права роли',
    'LBL_AUTH_SELECT_EDIT' => 'Все (Редактирование, Просмотр, Выполнение)',
    'LBL_AUTH_SELECT_VIEW' => 'Видеть (Просмотр, Выполнение)',
    'LBL_AUTH_SELECT_EXEC' => 'Выполнять (Выполнение)',
    'LBL_AUTH_SELECT_NONE' => 'Нет прав',

    'BTN_LBL_SAVE_PERMISSION' => 'Сохранить разрешения',
    'LBL_AUTHMANAGEMENT_INDIVIDUAL' => 'Включить управление доступом(если отключено, любой пользователь сможет просмотреть/запустить этот бизнес-процесс, а любой администратор сможет его редактировать)',
    'LBL_HELP' => 'Помощь',

    'LBL_DELETE_ORIGINAL_RECORD' => 'Удалить старую запись',
    'LBL_CREATE_INVENTORY' => 'Создать объект с Товарами',
    'LBL_CONVERT_TO_INVOICE' => 'Преобразовать в счет',
    'LBL_NO_WORKFLOWS' => 'Нет бизнес-процессов',

    'LBL_CURRENT_USER' => 'Текущий пользователь',
    'LBL_GROUP_TAX_IF_ENABLED' => 'Групповой налог, если разрешено',
    'LBL_SHIPPING_TAX' => 'Плата за доставку и обработку',
    'LBL_VALUE_CLEAR' => 'Очистить',
    'LBL_VALUE_RESET' => 'Сбросить',
    'LBL_SELECT_INPUT_INDIVIDUAL_VALUE' => 'Пользовательское значение',

    'LBL_VALUES' => 'Значения списка',
    'LBL_EMPTY_VALUE' => 'Очистить значение',

    'HEAD_STARTVARIABLE_REQUEST' => 'Запросить данные перед выполнением',
    'INFO_STARTVARIABLE' => 'Эти переменные будут запрошены только в случае выполнения бизнес-процесса из боковой панели (ручной запуск).',

    'LBL_INPUTTYPE_TEXT' => 'Текстовое поле',
    'LBL_INPUTTYPE_CHECKBOX' => 'Метка',
    'LBL_INPUTTYPE_SELECT' => 'Выпадающий список',
    'LBL_INPUTTYPE_DATE' => 'Дата',

    'LBL_INSERT_TEMPLATE_VARIABLE' => 'Выберите переменную, которую вы хотите вставить:',
    'LBL_DOUBLE_CLICK_TO_INCREASE_SIZE' => 'Дважды кликните мышкой, чтобы увеличть область текстового поля',

    'LBL_DIRECT_RUN' => 'Запустить сразу после подтверждения',
    'LBL_REFERENCES' => 'Ссылки',
    'LBL_ID_OF_CURRENT_RECORD' => 'ID текущей записи',

    'LBL_DOCUMENTATION' => 'Документация',

    'LBL_DUPLICATE' => 'Дублировать',

    'LBL_DUPLICATE_RECORD' => 'Дублировать запись',
    'LBL_USE_FOLLOWING_RECORDID' => 'Дублировать следующую запись',
    'LBL_DUPLICATE_RECORD_OF_MODULE' => 'Дублировать запись следующего модуля',

    'LBL_SETTINGS_TRIGGERMANAGER' => 'Управление триггерами',
    'LBL_SETTINGS_TRIGGERMANAGER_DESC' => 'Управляйте вашим триггерами',

    'HEAD_TRIGGER_MANAGER' => "Эти триггеры можно создавать для объединения нескольких бизнес-процессов в 'связку'. Это может быть полезно для запуска процессов вручную.",
    'LBL_CUSTOM_TRIGGER' => 'Пользовательский триггер',
    'LBL_SYS_TRIGGER' => 'Внутренний триггер',
    'LBL_NEW_TRIGGER' => 'Новый триггер',
    'LBL_CREATE_TRIGGER' => 'Создать триггер',

    'LBL_BUTTON_TEXTS' => 'Текст кнопки',
    'LBL_REDIRECT_USER' => 'Перенаправить пользователя на URL',
    'LBL_REDIRECT_TO_URL' => 'После завершения бизнес-процесса<br>перенаправить на этот URL:',

    'LBL_IMPORTER' => 'Импортирование',
    'LBL_WORKFLOW2_EXECUTE' => 'Выполнить бизнес-процесс',
    'LBL_IMPORTER_TRIGGER' => '6. После импорта',
    'LBL_CSV_GET_NEXT_LINE' => 'Следующая запись из CSV',

    'LBL_PAUSE_AFTER_RECORDS' => 'Пауза после х строк',
    'LBL_WHY_IMPORT_PAUSE' => 'Чтобы избежать прерывания задачи по таймауту',
    'LBL_IMPORT_IS_FINISHED' => 'Импорт окончен',
    'LBL_NO_CONFIG_FORM' => 'Нет конфигурации',
    'LBL_CONFIG_MODULE' => 'Настроить модуль',

    'LBL_MINIFY_LOGS_AFTER' => 'Архивировать статистику после',
    'LBL_REMOVE_LOGS_AFTER' => 'Удалить статистику после',

    'EXEC_FOLLOWING_WORKFLOW' => 'Выполнить следующий бизнес-процесс',
    'SORT_RESULTS_WITH' => 'Отсортировать результаты по этому столбцу',

    'LBL_TASK_SHOW_ALERT' => 'Показать сообщение',
    'LBL_ALERT_RECORD' => 'Связать сообщения с данной записью',
    'LBL_ALERT_ASSIGNED_USER' => 'Показать сообщение этому пользователю',
    'LBL_ALERT_TEXT' => 'Текст сообщения',

    'LBL_CAT_ENTDATA' => 'Значения данных записи',
    'LBL_TASK_ENTITYDATA_SET' => 'Записать данные',
    'LBL_TASK_ENTITYDATA_GET' => 'Считать данные',

    'LBL_SETTER_INDIVIDUAL' => 'Установить значение другой записи',
    'LBL_CAT_INDEPEND' => 'Независимые задачи',
    'LBL_UPDATE_DYNAMIC_BASETIME' => 'Обновить после начала задержки',

    'LBL_IMPORT_OVERWRITE_MODULE' => 'Импорт в следующий модуль',
    'LBL_IMPORT_OVERWRITE_MODULE_ACTIVATE' => 'Активировать изменение модуля',

    'LBL_TRIGGERNAME' => 'Имя',
    'LBL_TRIGGERKEY' => 'Ключ',

    'LBL_SETTINGS_HTTPHANDLER' => 'Настройки HTTP-обработчика',
    'LBL_SETTINGS_HTTPHANDLER_DESC' => 'Настройки безопасности для HTTP-обработчика. Security settings for HTTP Handler Feature',

    'LBL_LIMIT_HTTP_ACCESS_IP_HEAD' => 'Здесь вы можете ограничить доступ к HTTP-обработчику: установить несколько IP адресов и IP диапазонов, с которых можно его вызывать. Доступ из других источников вызовет сообщение об ошибке.',

    'LBL_HTTP_LIMIT_EDITOR_NAME'    => 'Заголовок границы',
    'LBL_HTTP_LIMIT_EDITOR_IPS'     => "Разрешенные IP адреса<br><span style='font-size:10px;'>один IP адрес/диапазон в строке<br><strong>Пример:</strong> <br>192.168.2.5<br>192.168.*.*<br>192.168.0.0/16<br>192.168.0.0-192.168.0.255</span>",
    'LBL_HTTP_LIMIT_EDITOR_WORKFLOWS'     => 'Бизнес-процессы, которые можно запускать с этого IP адреса',
    'LBL_HTTP_LIMIT_EDITOR_TRIGGERS'     => 'Триггеры, которые можно запускать с этого IP адреса',

    'LBL_ADD_HTTP_LIMIT'            => 'Новое Разрешение',

    'LBL_WARNING_LICENSE_COUNT' => 'Ваша лицензия не позволяет создавать большее количество бизнес-процессов. Чтобы создать больше бизнес-процессов, вам необходимо обновить вашу лицензию.',
    'LBL_SAVED_SUCCESSFULLY' => 'Успешно сохранено',

    'LBL_LICENSE_ACTIVATION_HINT' => 'Вы сменили имя хоста вашей CRM или обновили конструктор бизнес-процессов.',
    'LBL_LICENSE_ACTIVATION_HINT2' => 'Вы успешно активировали лицензионную версию продукта!',
    'LBL_LICENSE_ACTIVATION_DEMO_HINT2' => 'Вы успешно активировали пробную бесплатную версию!',

    'LBL_EXECUTION_TYPE' => 'Тип запуска',
    'LBL_MANUELL' => 'Вручную',
    'LBL_AUTOMATIC' => 'Автоматически',

    'LBL_SMSNOTIFIER_SEND' => 'Отправить СМС',
    'LBL_PHONE_NUMBER' => 'Номер получателя',
    'LBL_SMS_TEXT' => 'Текст СМС',
    'LBL_LETTER' => 'Символов',

    'LBL_CHANGE_BLOCKCOLOR' => 'Изменить цвет',
    'LBL_REMOVE_BLOCKCOLOR' => 'Удалить цвет',

    'LBL_TEXTBLOCK' => 'Текстовый блок',
    'LBL_GET_KNOWN_ENVVARS' => 'Распознать переменные окружения',

    'LBL_CURRENTLY_RUNNING' => 'Запущено',
    'LBL_CURRENTLY_RUNNING_DESCR' => 'Число выполнений/операций в настоящее время отложено/задержано и будет продолжено/выполнено позже.',
    'LBL_LAST_ERRORS'   => 'Последние ошибки',
    'LBL_LAST_ERRORS_DESCR'   => 'Ошибки в этом бизнес-процессе за последние 14 дней',

    'HEAD_ERRORS_FOR_WORKFLOW' => 'Ошибки бизнес-процесса',
    'LBL_OPTIONEN' => 'Опции',

    'LBL_TASK_MYSQL_QUERY' => 'Выполнить и Сохранить результаты запроса MySQL',
    'LBL_MYSQL_QUERY_ENV_VARIABLE' => 'Сохранить результат в переменной окружения $env',

    'TXT_EXIT_INFO' => "Вы видите эту страницу потому, что процесс был остановлен <b>'%s'</b> (Задача: %s)",
    'LBL_REVERSE_INVENTORY' => 'Отменить счет',

    'LBL_REVERSE_CREATE_INVENTORY_EXPLAIN' => 'Этот блок дублирует текущий объект с Товарами и умножает количества каждого Товара на -1 (делает отрицательным), чтобы полностью отменить счет',

    'LBL_REDIRECT_TO_URL_TARGET' => 'Целевое окно',
    'LBL_REDIRECT_TO_URL_TARGET_DESCR' => 'Эта опция влияет только на действия из боковой панели. Перенаправления в бизнес-процессах, которые будут выполняться при каждом сохранении или создании, будут всегда открвать ссылку в том же окне.',

    'LBL_REDIRECT_TO_PDFMAKER' => 'После завершения этого бизнес-процесса<br>загрузить шаблон из PDFMaker',

    'LBL_ACTIVATE_SIDEBAR' => 'Включить боковую панель',
    'LBL_DEACTIVATE_SIDEBAR' => 'Выключить боковую панель',

    'LBL_DELETE_SET_FIELD' => '::: Удалить строку :::',

    'HEAD_VISIBLE_CONDITION' => 'Показать/выполнить бизнес-процесс, если условия выполняются',
    'LBL_ADD_PRODUCTS_BY_ARRAY' => 'Добавить продукты из массива',

    'LBL_SAVE_PDF_DOCUMENT' => 'Сохранить шаблон PDF-Maker',
    'LBL_FOR_THIS_YOU_NEED_PDFMAKER' => 'Для этого задания вам необходим PDFMaker от its4you!',

    'LBL_DOCUMENT_TITLE' => 'Заголовок документа',
    'LBL_DOCUMENT_DESCR' => 'Описание документа',
    'LBL_FOLDER' => 'Папка',
    'LBL_PDFTEMPLATE' => 'PDF-шаблон',
    'LBL_CREATE_RELATION' => 'Связать с текущей записью',
    'LBL_FORCE_WORKFLOW' => 'Выполнить бизнес-процесс<br>для этой записи',
    'LBL_OVERWRITE_FILENAME' => 'Переопределить имя PDF-файла<br><em>оставьте пустым для имени по умолчанию из PDFMaker</em>',
    'LBL_SET' => 'Установить',
    'LBL_SET_ALL_ROLE_PERMISSIONS' => 'Установить все права пользователей на',
    'LBL_SET_ALL_USER_PERMISSIONS' => 'Установить все права пользователей на',
    'LBL_TASK_MANAGEMENT' => 'Управление задачами',
    'LBL_TASK_REPO_MANAGEMENT' => 'Repository management',
    'LBL_ADD_REPOSITORY'        => 'Добавить репозиторий',

    'LBL_REPO_URL'          => 'URL репозитория',
    'LBL_REPO_LICENSEKEY'          => 'Лицензионный ключ<br><span style="font-size:10px;">(если требуется)</span>',
    'LBL_REPO_TITLE'    => 'Имя репозитория',
    'LBL_TASK_REPO_UPDATE' => 'Обновить репозитории',
    'LBL_HTTP_LIMIT_URL'        => 'Главный/Целевой URL',
    'LBL_ALREADY_SET_KEY'       => 'Вы уже активировали этот лицензионный ключ.',

    /* New to vt6 */

    'LBL_MESSAGE_TITLE'         => 'Заголовок сообщения',
    'LBL_MESSAGE_CONTENT'       => 'Сообщение',
    'LBL_MESSAGE_TYPE'          => 'Тип сообщения',
    'LBL_MESSAGE_TYPE_SUCCESS'  => 'Успешно',
    'LBL_MESSAGE_TYPE_INFO'     => 'Совет',
    'LBL_MESSAGE_TYPE_ERROR'    => 'Ошибка',
    'LBL_MESSAGE_SHOW_ONCE'     => 'Показать один раз',
    'LBL_MESSAGE_SHOW_UNTIL'    => 'Показывать каждый раз, пока не...',
    'LBL_MESSAGE_EXPLAIN'       => 'Чтобы использовать эти возможности, вам необходимо активировать боковую панель в этом модуле!',
    'LBL_VISIBLE_UNTIL'         => 'Видим до тех пор, пока не',
    'LBL_MESSAGE_POSITION'      => 'Координаты сообщения',
    'LBL_POS_TOP'           => 'Вверх',
    'LBL_POS_CENTER'      => 'В центр',
    'LBL_POS_BOTTOM'      => 'Вниз',
    'LBL_POS_LEFT'           => 'Влево',
    'LBL_POS_RIGHT'      => 'Вправо',
    'LBL_MESSAGE_TARGET'      => 'Сообщение относится к данному CRMID.<br/>Если не заполнено, использовать текущую запись.',
    'LBL_SETTINGS_SCHEDULER'      => 'Диспетчер бизнес-процессов',
    'LBL_SETTINGS_SCHEDULER_DESC'      => ' Запланируйте повторяющиеся бизнес-процессы',
    'LBL_TASK_MESSAGE'          => 'Показывать уведомление на странице записи',
    'LBL_TASK_IMPORT_FILE'  => 'Импортировать задачу',
    'LBL_CHOOSE_TASKFILE'   => 'Выбрать задачу',
    'LBL_UPGRADE_EXISTING'  => 'Обновить существующую задачу, если она имеется',
    'LBL_UPGRADE_EVEN_OLDER'  => 'Обновить существующую задачу, даже если существуют задачи новее',

    'TXT_CLICK_ON_PATH' => 'Нажмите на путь для более подробной информации',

    'LBL_LICENSE_MANAGER'   => 'Управление лицензиями',
    'LBL_LICENSE_IS'        => 'Вид лицензии',
    'LBL_LICENSE_STATE'     => 'Версия лицензии',
    'LBL_REVALIDATE_LICENSE' => 'Перепроверить лицензию',
    'LBL_REMOVE_LICENSE'    => 'Удалить лицензию',

    'LBL_CREATE_TYPE'   => 'Создать новый блок вручную',
    'LBL_WRITE_NUMBER_IN_FIELD' => 'Записать в это поле',
    'LBL_COUNT_SERIE'       => 'Количество строк',
    'LBL_NEXT_NUMBER'       => 'Следующий ID',
    'LBL_SERIE_STRLENGTH'   => 'Длина номера',
    'LBL_SERIE_NEXT'        => 'Следующий ID',
    'LBL_SERIE_PREFIX'      => 'Префикс номера',
    'LBL_EXECUTE_WORKFLOW_ONLY_ONCE_PER_RECORD' => 'Только один запуск для записи',

    'LBL_TASK_REQUEST_VALUES' => 'Запросить данные от пользователя',

    'LBL_TASK_CONVERT_RECORD' => 'Преобразовать запись',

    'LBL_TASK_PUSHOVER' => 'Отправить уведомление Pushover',
    'LBL_PUSHOVER_USERID' => 'Ваш ключ для Pushover',
    'LBL_PUSHOVER_APPKEY_OPTIONAL' => '(дополнительно) Ваш личный Pushover AppKey',
    'LBL_PUSHOVER_INTRO' => 'Этот блок будет посылать уведомления на ваш прямо на вашем мобильном устройстве Pushover. У вас уже должна быть <a href="https://pushover.net/" target="_blank">Учетная запись Pushover</a>!<br/> AppKey нужен только если вы хотите сменить имя в Pushover App на вашем мобильном устройстве.',
    'LBL_PUSHOVER_TARGET_DEVICE' => 'Отправить уведомление на это устройство',

    'LBL_DEACTIVATE_REDIRECT' => 'Отключить перенаправление',
    'LBL_WORKFLOW_IS_ACTIVE' => 'Бизнес-процесс активен?',
    'LBL_ERROR_REPORT' => 'Сообщить об ошибке',

    'Upload new *.docx File' => 'Загрузить файл формата *.docx',
    'configure placeholders' => 'Формат ввода',

    'Frontend Manager' => 'Управление кнопками',
    'add Workflow' => 'Добавить бизнес-процесс',
    'choose a Workflow' => 'Выберите бизнес-процесс',
    'custom RecordID' => 'Указать ID записи',
    'generate custom recordID' => 'Создать пользовательскую запись',
    'LBL_FILTER_RECORDS_2_SELECT' => 'Это условие отфильтрует записи, которые вы могли выбрать из меню пользовательского интерфейса',
    'module of records' => 'модуль записей',
    'select Record' => 'Выбрать запись',
    'Records from module' => 'Записи из модуля',
    'Search possible Records' => 'Поиск возможных записей',
    'choose fields to check' => 'Выберите поля для проверки',
    'update these fields if duplicate found' => 'Обновить эти поля для первой найденной записи',
    'The task will check the configured fields, before creating a new record. If the task found already some records with equal fieldvalues, no new record will be created.' => 'Задание проверит указанные поля перед созданием новой записи. Если будут найдены записи с совпадающими значениями полей, то новые записи созданы не будут.',
    'duplicate record check' => 'Проверка на дубликаты',
    'Because of Login Restrictions, you need to do the Login and Authorization within the PopUp and copy the Code you get in this Textfield.' => 'Из-за ограничений авторизации вам необходимо авторизоваться во всплывающем окне и скопировать код из текстового поля.',
    'LBL_GOOGLE_CREATE_EVENT' => 'Создать событие в google-календаре',

    'LBL_CALENDAR' => 'Календарь',
    'LBL_EVENT_DESCR' => 'Описание',
    'LBL_EVENT_TITLE' => 'Название события',

    'LBL_EVENT_START_DATE' => 'Дата начала (гггг-мм-дд)',
    'LBL_EVENT_START_TIME' => 'Время начала (чч:мм)',
    'LBL_EVENT_DURATION' => 'Продолжительность (минут)',

    'LBL_PRIV_DEFAULT' => 'По умолчанию',
    'LBL_PRIV_PUBLIC' => 'Публичное',
    'LBL_PRIV_PRIVATE' => 'Личное',

    'LBL_PRIVACY' => 'Видимость',
    'execute expression on products' => 'Выполнить выражение для товаров',

    'Workflow2'                      => 'Конструктор бизнес-процессов',

    'LBL_FORCE_EXECUTION'       => 'Выполнить бизнес-процесс',
    'BTN_SHOW_ENTITYDATA'       => 'Данные записи',
    'LBL_ENTITYDATA_MODIFYDATE' => 'Изменено',
    'LBL_START' => 'Начать',
    'HINT_NO_ACTIVE_IMPOR_WORKFLOWS' => 'У вас нет активных процессов импорта!',
    'LBL_PLEASE_CHOOSE_IMPORT_WORKFLOW' => 'Пожалуйста, выберите бизнес-процесс, который вы хотите запустить',
    'LBL_PLEASE_CHOOSE_IMPORT_FORMAT' => 'Пожалуйста, выберите формат файла.',
    'LBL_PLEASE_CHOOSE_IMPORT_FILE' => 'Выберите импортируемый файл. (только формата CSV)',
    'HINT_FILE_IMPORT_PREVIEW'      => 'Предпросмотр файла',
    'HINT_FILE_IMPORT_PREVIEW_DESCR'      => 'Здесь отображается содержимое загруженного файла, как его видит система. Если не хватает столбцов,настройте параметры импорта.',
    'BTN_START_IMPORT'  => 'Начать импорт',
    'BTN_SET_IMPORT_CONFIG' => 'Настроить/Установить конфигурацию импорта',
    'TXT_IMPORT_DONE'       => 'Импорт завершен.',
    'BTN_BACK_TO_LAST_URL'  => 'Назад, к предыдущей странице',
    'LBL_UPDATE_WILL_INSTALLED' => 'Обновление будет установлено ...',
    'LBL_MODULE_WILL_INSTALLED' => 'Модуль будет инициализирован ...',
    'LBL_NEED_USERACCESS' => 'Следующие записи требуют от пользователя действий.',
    'LBL_ENTER_VALUES_TO_START' => 'Чтобы начать бизнес-процесс, пожалуйста, введите следующие данные',
    'LBL_DO_ACTION' => 'Действия',
    'Eingestellt' => 'Запрашивать после',
    'Bearbeitet' => 'Обработано',
    'Aktionen' => 'Действия',
    'choose Reference' => 'Ссылка',
    'no Selection' => 'Ничего не выбрано',
    'read documentation for more information' => 'Для детальной информации прочтите документацию',
    'recordlist ID' => 'список ID записей',
    'export this fileformat' => 'Экспортировать в выбранный формат',
    'export records' => 'Экспортировать записи',
    'insert Headline into first row' => 'Вставить заголовок в первую строку',
    'export this fields' => 'Экспортировать эти поля',
    'filename of generated file' => 'Имя файла',
    'add files into the zip file' => 'Архивировать файлы (ZIP)',
    'allow execution without a related record' => 'Разрешить выполнение без указания записи',
    'add files to upload' => 'Добавить файлы в загрузку',
    'Bricht komplette AusfÃ¼hrung ab' => 'Отладочный выход',
    'Bricht komplette Ausführung ab' => 'Отладочный выход',
    'LBL_BEFOREDELETE_TRIGGER' => '7. Перед удалением',
    'Separator' => 'Разделитель',
    'Reporting for this block' => 'Reporting for this block',
    'teststring for translation' => 'тестовая строка для перевода',
    'Module' => 'Модуль',
    'Record ID' => 'ID записи',
    'Record' => 'Запись',
    'Workflow' => 'Бизнес-процесс',
    'Block' => 'Блок',
    'disable complete Workflow List in Sidebar' => 'disable complete Workflow List in Sidebar',
    'Workflows' => 'Workflows',
    'search in available types' => 'поиск среди доступных типов',
    'stop all running instances' => 'остонавить все запущенные экземпляры',
    'You have deactivate the workflow. But already running instances will be executed nevertheless.' => 'You have deactivate the workflow. But already running instances will be executed nevertheless.',
    'Filestore Entry Action' => 'Работка с хранилищем файлов',
    'call Webservice' => 'вызвать Webservice',
    'parse Postalcode' => 'Распознать индекс',
    'User' => 'Пользователь',
    'Assign message to record or user' => 'Связать сообщение с записью или с пользователем',
    'Assign message to this user' => 'Вывести сообщение этому пользователю',
    'assigned to User' => 'Назначено Пользователю',
    'modified by User' => 'Изменено Пользователем',
    'able to input later and pause workflow' => 'able to input later and pause workflow',
    'able to completely stop workflow' => 'able to completely stop workflow',
    'open' => 'открыть',
    'selected records' => 'выбранные записи',
    'execute process only once with all checked records in listview' => 'execute process only once with all checked records in listview',
    'Main Module' => 'Главный Модуль',
    'create Workflow' => 'Создать бизнес-процесс',
    'reload current Page' => 'обновить страницу в браузере',
    'add other Object' => 'добавить другой объект',
    'calculate number of records' => 'посчитать количество записей',
    'Records found' => 'Записей найдено',
    'LBL_SHOW_INACTIVE' => 'показать неактивные блоки',
    'LBL_REFERENCE_TRIGGERED' => 'Reference to Record was set',
    'Executed after a record was saved' => 'Executed after a record was saved',
    'Executed after a record was created' => 'Executed after a record was created',
    'Executed if you send an email to the record' => 'Executed if you send an email to the record',
    'Executed if you create a new comment' => 'Executed if you create a new comment',
    'Only used for file import processing' => 'Only used for file import processing',
    'Executed directly after choosing a reference in editor' => 'Executed directly after choosing a reference in editor',
    'Label' => 'Label',
    'Include Type' => 'Include Type',
    'visible in Listview' => 'visible in Listview',
    'Button Color' => 'Button Color',
    'Sidebar Button' => 'Sidebar Button',
];

$jsLanguageStrings = [
    'LBL_DUPLICATE_BLOCK' => 'Дублировать блок',
    'LBL_DELETE_BLOCK' => 'Удалить блок',
    'LBL_CHANGE_BLOCKCOLOR' => 'Изменить цвет',
    'LBL_REMOVE_BLOCKCOLOR' => 'Удалить цвет',
    'HEAD_USAGE_OF_THIS_CONNECTION' => 'Использовать это соединение',
    'LBL_DATE' => 'Дата',
    'TXT_CHOOSE_VALID_FIELD' => 'Выберите поле',
    'LBL_MANAGE_SIDEBARTOOGLE' => 'Конструктор бизнес-процессов обрабатывает ваши данные',
    'LBL_CREATE_TYPE'   => 'Создать новый блок вручную',
    'LBL_SAVED_SUCCESSFULLY' => 'Успешно сохранено',
    'page' => 'Страница',
    'select all of this type' => 'Выбрать все этого типа',
    'LBL_PASTE_BLOCK' => 'Вставить блоки',
    'LBL_COPY_BLOCK' => 'Скопировать блоки',
];
