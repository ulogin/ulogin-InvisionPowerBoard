=== uLogin - виджет авторизации через социальные сети ===
Donate link: http://ulogin.ru/
Tags: ulogin, login, social, authorization
Requires at least: 3.1.4
Tested up to: 3.2.3
Stable tag: 1.7
License: GPL3
Форма авторизации uLogin через социальные сети. Улучшенный аналог loginza.

== Description ==

uLogin — это инструмент, который позволяет пользователям получить единый доступ к различным Интернет-сервисам без необходимости повторной регистрации,
а владельцам сайтов — получить дополнительный приток клиентов из социальных сетей и популярных порталов (Google, Яндекс, Mail.ru, ВКонтакте, Facebook и др.)

== Installation ==

Установка ULogin на IPBoard

1. Скопировать папку uloginplugin в дирректорию /admin/sources/loginauth
2. Создать в базе данных таблицу с именем prefix_ulogin. Вместо Prefix_ нужно написать префикс таблиц, который вы указали при установке (или опустить, если префикс не указывался)
	Поля таблицы: 
		ID - int, Auto Increment
		ident - varchar, Not Null
		id_user - int, Not Null 
		seed - int, unsigned
Это можно сделать, выполнив следующий скрипт в базе данных (опять же, заменив prefix_ на ваш префикс)

CREATE TABLE `prefix_ulogin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ident` varchar(255) NOT NULL,
  `id_user` int(11) NOT NULL,
  `seed` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=18 ;





  
3. Зайти в Админцентр
4. На вкладке "Система" выбрать "Управление модулями авторизации"
5. Добавить метод со следующими параметрами:
	Название "uloginplugin"
	Имя директории с файлами модуля "uloginplugin"
	Модуль включен? "Да"
	HTML код формы:

		Зайдите на вашем форуме на страницу с формой авторизации при помощи браузера и нажмите "показать html-код страницы". Найдите в тексте поле с именем auth_key, скопируйте значение атрибута value.
		Зайдите по адресу html://ulogin.ru/constructor.html и в поле "Адрес обратной ссылки на ваш сайт" пропишите:
		http://адрес-вашего-форума/index.php?app=core&module=global&section=login&do=process&auth_key=value
		где вместо value нужно вписать код, полученный на предыдущем шаге.

		Появившийся в поле "Код для вставки в страницу логина" код скопируйте в любой текстовый редактор, и после текста "first_name,last_name,photo" добавьте ",email".
		Это и есть код, который требовалось получить.

	HTML код формы для замены "нет"

6. Зайти в редактор шаблонов в (Внешний вид->IP.Board)
7. Выбрать шаблон globalTemplate
8. В начале, после тега <head> вставить
 <style type="text/css">
      #uLogin img{ vertical-align: top !important;}
  </style>
9. Для версии 3.2.3:

После кода

<a href='{parse url="app=core&amp;module=global&amp;section=login" base="public"}' title='{$this->lang->words['sign_in']}' id='sign_in' <if test="IPS_APP_COMPONENT == 'ccs'">class='no_ajax'</if>>{$this->lang->words['sign_in']}</a>&nbsp;&nbsp;&nbsp;
								</li>

добавить <li>код, полученный на шаге 5</li>




Для версии 3.1.4:

Заменить код

{$this->lang->words['new_user']}
							<a href="{parse url="app=core&amp;module=global&amp;section=register" base="public"}" title='{$this->lang->words['register']}' id='register_link'>{$this->lang->words['register']}</a>
							<a href="{parse url="app=core&amp;module=help" base="public"}" title='{$this->lang->words['view_help']}' rel="help" accesskey='6' class='right'>{parse replacement="help_icon"} {$this->lang->words['sj_help']}</a>

на код для HTML-формы, полученный на шаге 5.
