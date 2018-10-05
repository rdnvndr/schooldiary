# SchoolDiary
Программа получения расписание уроков и оценок на неделю из электронного дневника 
московской области https://schools.school.mosreg.ru для отправки на почту или отображения в браузере.

## Настройка
Для настройки необходимо отредактировать файл diary.php и указать значение переменных:
```
 $site         = "http://www.yourhost.com/diary.php"; // Сайт с файлом diary.php
 $school       = "1234567890123";                    // Идентификатор школы
 $yourlogin    = "yourlogin";                         // Ваш логин в электронном дневнике
 $yourpassword = "yourpassword";                      // Ваш пароль в электронном дневнике
 $email        = "email@example.ru";                  // E-mail для отправки расписания
```

## Использование
Для получения расписания необходимо в браузере перейти по ссылке вида:
```
http://www.yourhost.com/diary.php?year=2018&month=09&day=22&dst=web
```
Параметры в ссылке:
 * year, month, day - день недели, при отсутствии текущий день
 * dst - место отправки расписания. Значение web для отображения в браузере, в противном случае e-mail 
 
 ## Лицензия
 Информацию по лицензии смотрите в файле LICENSE
