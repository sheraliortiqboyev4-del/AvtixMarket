-- phpMyAdmin SQL Dump
-- version 5.2.1-1.el8
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Сен 01 2026 г., 14:15
-- Версия сервера: 10.6.27-MariaDB-cll-lve
-- Версия PHP: 7.2.24

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `681edb1c98b2a_stars`
--

-- --------------------------------------------------------

--
-- Структура таблицы `adminlar`
--

CREATE TABLE `adminlar` (
  `id` int(11) NOT NULL,
  `foydalanuvchi_id` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `adminlar`
--

INSERT INTO `adminlar` (`id`, `foydalanuvchi_id`) VALUES
(1, '2142292702');

-- --------------------------------------------------------

--
-- Структура таблицы `admin_logs`
--

CREATE TABLE `admin_logs` (
  `id` int(11) NOT NULL,
  `admin_id` bigint(20) NOT NULL,
  `action` varchar(100) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `api_keys`
--

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `api_key` varchar(64) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `last_used_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `api_usage`
--

CREATE TABLE `api_usage` (
  `id` int(11) NOT NULL,
  `api_key_id` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `endpoint` varchar(128) NOT NULL,
  `status` enum('success','failed') NOT NULL,
  `price` decimal(18,2) DEFAULT 0.00,
  `request_ip` varchar(64) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `bots`
--

CREATE TABLE `bots` (
  `id` int(11) NOT NULL,
  `bot_tokenn` varchar(255) NOT NULL,
  `bot_link` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `gifts`
--

CREATE TABLE `gifts` (
  `id` int(11) NOT NULL,
  `gift_id` varchar(64) NOT NULL,
  `sticker_id` varchar(64) DEFAULT NULL,
  `emoji` varchar(16) DEFAULT NULL,
  `stars` int(11) NOT NULL,
  `price_uzs` decimal(18,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `joinRequest`
--

CREATE TABLE `joinRequest` (
  `id` int(11) NOT NULL,
  `user_id` varchar(202) NOT NULL,
  `chat_id` varchar(202) NOT NULL,
  `status` varchar(55) NOT NULL DEFAULT 'left'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `kanal`
--

CREATE TABLE `kanal` (
  `id` int(11) NOT NULL,
  `chat_id` varchar(50) NOT NULL,
  `kanal_url` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `majburiysiz`
--

CREATE TABLE `majburiysiz` (
  `id` int(11) NOT NULL,
  `link` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `order_id` varchar(255) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `username` varchar(255) NOT NULL,
  `quantity_ton` varchar(300) NOT NULL,
  `hamyon_ton` varchar(300) NOT NULL,
  `quantity` varchar(255) NOT NULL,
  `mountity` varchar(255) NOT NULL,
  `gift` text NOT NULL,
  `custom_emoji` varchar(255) NOT NULL,
  `gift_id` varchar(255) NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `ton_amount` decimal(18,9) NOT NULL,
  `status` enum('pending','paid','cancelled') DEFAULT 'pending',
  `turi` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `payme_credentials`
--

CREATE TABLE `payme_credentials` (
  `id` int(11) NOT NULL,
  `merchant_id` varchar(255) NOT NULL,
  `secret_key` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `payme_transactions`
--

CREATE TABLE `payme_transactions` (
  `id` int(11) NOT NULL,
  `payme_id` varchar(64) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `amount` decimal(18,2) NOT NULL,
  `amount_tiyin` bigint(20) NOT NULL,
  `state` tinyint(4) NOT NULL DEFAULT 1,
  `reason` tinyint(4) DEFAULT NULL,
  `create_time` bigint(20) NOT NULL,
  `perform_time` bigint(20) DEFAULT 0,
  `cancel_time` bigint(20) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `prices`
--

CREATE TABLE `prices` (
  `id` int(11) NOT NULL,
  `product_key` varchar(64) NOT NULL,
  `title` varchar(255) NOT NULL,
  `price` decimal(18,4) NOT NULL,
  `updated_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `sendusers`
--

CREATE TABLE `sendusers` (
  `id` int(11) NOT NULL,
  `unique_id` varchar(255) NOT NULL,
  `mid` varchar(255) NOT NULL,
  `boshlash_vaqt` varchar(10) NOT NULL,
  `soni` int(11) NOT NULL DEFAULT 0,
  `joriy_vaqt` varchar(10) NOT NULL,
  `status` enum('active','cancelled','done') NOT NULL DEFAULT 'active',
  `send` int(11) NOT NULL DEFAULT 0,
  `holat` varchar(50) NOT NULL DEFAULT 'copyMessage',
  `nosend` int(11) NOT NULL DEFAULT 0,
  `button` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `payme_id` varchar(255) NOT NULL,
  `payme_time` bigint(20) NOT NULL,
  `amount` bigint(20) NOT NULL,
  `account` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`account`)),
  `order_id` int(11) DEFAULT NULL,
  `state` int(11) DEFAULT 1,
  `create_time` bigint(20) NOT NULL,
  `perform_time` bigint(20) DEFAULT NULL,
  `cancel_time` bigint(20) DEFAULT NULL,
  `transaction_id` varchar(255) NOT NULL,
  `reason` int(11) DEFAULT NULL,
  `receivers` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`receivers`)),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `user_id` bigint(20) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `username` varchar(255) DEFAULT NULL,
  `ref_id` varchar(50) DEFAULT NULL,
  `user_ref_id` varchar(50) DEFAULT NULL,
  `captcha` int(11) DEFAULT NULL,
  `captcha_required` tinyint(1) DEFAULT 0,
  `captcha_passed` tinyint(1) DEFAULT 0,
  `captcha_time` int(11) NOT NULL,
  `captcha_try` varchar(50) NOT NULL,
  `balance` int(11) DEFAULT 0,
  `ref` int(11) DEFAULT 0,
  `sana` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `zayafka`
--

CREATE TABLE `zayafka` (
  `id` int(11) NOT NULL,
  `chat_id` bigint(20) NOT NULL,
  `invite_link` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `adminlar`
--
ALTER TABLE `adminlar`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `api_keys`
--
ALTER TABLE `api_keys`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`),
  ADD UNIQUE KEY `api_key` (`api_key`);

--
-- Индексы таблицы `api_usage`
--
ALTER TABLE `api_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usage_key_time` (`api_key_id`,`created_at`);

--
-- Индексы таблицы `bots`
--
ALTER TABLE `bots`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `gifts`
--
ALTER TABLE `gifts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `gift_id` (`gift_id`);

--
-- Индексы таблицы `joinRequest`
--
ALTER TABLE `joinRequest`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_channel` (`user_id`,`chat_id`);

--
-- Индексы таблицы `kanal`
--
ALTER TABLE `kanal`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `majburiysiz`
--
ALTER TABLE `majburiysiz`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `order_id` (`order_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_orders_status` (`status`),
  ADD KEY `idx_orders_user_id` (`user_id`),
  ADD KEY `idx_orders_turi` (`turi`),
  ADD KEY `idx_orders_created_at` (`created_at`);

--
-- Индексы таблицы `payme_credentials`
--
ALTER TABLE `payme_credentials`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `merchant_id` (`merchant_id`);

--
-- Индексы таблицы `payme_transactions`
--
ALTER TABLE `payme_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payme_id` (`payme_id`);

--
-- Индексы таблицы `prices`
--
ALTER TABLE `prices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `product_key` (`product_key`);

--
-- Индексы таблицы `sendusers`
--
ALTER TABLE `sendusers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_id` (`unique_id`);

--
-- Индексы таблицы `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `payme_id` (`payme_id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_payme_id` (`payme_id`),
  ADD KEY `idx_order_id` (`order_id`),
  ADD KEY `idx_state` (`state`),
  ADD KEY `idx_transactions_payme_id` (`payme_id`),
  ADD KEY `idx_transactions_state` (`state`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Индексы таблицы `zayafka`
--
ALTER TABLE `zayafka`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `adminlar`
--
ALTER TABLE `adminlar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT для таблицы `api_keys`
--
ALTER TABLE `api_keys`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `api_usage`
--
ALTER TABLE `api_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `bots`
--
ALTER TABLE `bots`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `gifts`
--
ALTER TABLE `gifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `joinRequest`
--
ALTER TABLE `joinRequest`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `kanal`
--
ALTER TABLE `kanal`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `majburiysiz`
--
ALTER TABLE `majburiysiz`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=355;

--
-- AUTO_INCREMENT для таблицы `payme_credentials`
--
ALTER TABLE `payme_credentials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT для таблицы `payme_transactions`
--
ALTER TABLE `payme_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `prices`
--
ALTER TABLE `prices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `sendusers`
--
ALTER TABLE `sendusers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `zayafka`
--
ALTER TABLE `zayafka`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
