
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
CREATE TABLE `activity_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `userId` int(11) DEFAULT NULL,
  `roleId` int(11) DEFAULT NULL,
  `action` varchar(50) NOT NULL,
  `entityType` varchar(50) NOT NULL,
  `entityId` int(11) DEFAULT NULL,
  `description` varchar(255) NOT NULL,
  `details` text,
  `ipAddress` varchar(45) DEFAULT NULL,
  `created` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cafe_tables` (
  `id` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `seats` tinyint(3) UNSIGNED NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `deleted` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `cafe_tables` (`id`, `number`, `seats`, `active`, `created`, `deleted`) VALUES
(1, 1, 2, 1, '2026-07-20 16:53:03', NULL),
(2, 2, 2, 1, '2026-07-20 16:53:03', NULL),
(3, 3, 4, 1, '2026-07-20 16:53:03', NULL),
(4, 4, 4, 1, '2026-07-20 16:53:03', NULL),
(5, 5, 6, 1, '2026-07-20 16:53:03', NULL);

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `created` datetime NOT NULL,
  `deleted` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `categories` (`id`, `name`, `created`, `deleted`) VALUES
(4, 'Салаты', '2026-07-19 18:42:01', NULL);

CREATE TABLE `menu` (
  `id` int(11) NOT NULL,
  `name` varchar(250) NOT NULL,
  `photo` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `structure` text NOT NULL,
  `cooktime` smallint(5) UNSIGNED NOT NULL DEFAULT '10',
  `isStopped` tinyint(1) NOT NULL DEFAULT '0',
  `stoppedAt` datetime DEFAULT NULL,
  `categoryId` int(11) NOT NULL,
  `created` datetime NOT NULL,
  `deleted` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `menu` (`id`, `name`, `photo`, `description`, `structure`, `cooktime`, `isStopped`, `stoppedAt`, `categoryId`, `created`, `deleted`) VALUES
(2, 'Салат коул слоу классический с соусом', '6a5cf3b1545c9.jpg', 'Витаминный, простой, но очень вкусный. На скорую руку. Для салата Коул слоу классического потребуется минимальный набор доступных продуктов. Главное его отличие от других подобных блюд - это заправка, делающая его очень аппетитным и сочным.', 'Капуста белокочанная Морковь Стебель сельдерея Сметана Майонез Горчица Соль Перец черный молотый Сахар', 10, 0, NULL, 4, '2026-07-19 18:56:33', NULL);

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `tableId` int(11) NOT NULL,
  `reservationId` int(11) DEFAULT NULL,
  `waiterId` int(11) DEFAULT NULL,
  `statusId` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `updated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `orderId` int(11) NOT NULL,
  `dishId` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT '1',
  `comment` varchar(500) DEFAULT NULL,
  `statusId` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `updated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `order_statuses` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(50) NOT NULL,
  `position` tinyint(3) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `order_statuses` (`id`, `code`, `name`, `position`) VALUES
(1, 'new', 'Новый', 1),
(2, 'cooking', 'Готовится', 2),
(3, 'ready', 'Готов', 3),
(4, 'completed', 'Выдан', 4),
(5, 'cancelled', 'Отменён', 5);

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `tableId` int(11) NOT NULL,
  `guests` tinyint(3) UNSIGNED NOT NULL,
  `reservationAt` datetime NOT NULL,
  `durationMinutes` smallint(5) UNSIGNED NOT NULL DEFAULT '120',
  `statusId` tinyint(3) UNSIGNED NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `updated` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `reservation_statuses` (
  `id` tinyint(3) UNSIGNED NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `reservation_statuses` (`id`, `code`, `name`) VALUES
(1, 'new', 'Новая'),
(2, 'confirmed', 'Подтверждена'),
(3, 'cancelled', 'Отменена'),
(4, 'completed', 'Завершена');

CREATE TABLE `role` (
  `id` int(11) NOT NULL,
  `roleName` varchar(200) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `role` (`id`, `roleName`, `description`) VALUES
(1, 'Пользователь', 'Посетитель кафе'),
(2, 'Повар', 'Работник кухни'),
(3, 'Официант', 'Обслуживание столов'),
(4, 'Менеджер', 'Управление кафе'),
(5, 'Администратор', 'Доступ ко всем функциям');

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `login` varchar(200) NOT NULL,
  `name` varchar(200) NOT NULL,
  `phone` varchar(200) NOT NULL,
  `password` varchar(200) NOT NULL,
  `roleId` int(11) NOT NULL DEFAULT '1',
  `created` datetime NOT NULL,
  `deleted` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `users` (`id`, `login`, `name`, `phone`, `password`, `roleId`, `created`, `deleted`) VALUES
(1, 'qw', 'qw', 'qw', '$2y$10$.namtwdpuibJwEjL99PZHuofXnd.kgfoNih52ece2Gi2Nvyei0Keu', 5, '2026-07-19 00:39:25', NULL),
(2, 'as', 'as', '123', '$2y$10$D9xJDXZ.S3HaXdwBdH9NoeFerxL8xQZrfKAt6y9Cr1YEj9J7V3q8e', 1, '2026-07-21 02:30:58', NULL);
ALTER TABLE `cafe_tables`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `number` (`number`);
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `activity_user` (`userId`),
  ADD KEY `activity_action` (`action`),
  ADD KEY `activity_entity` (`entityType`,`entityId`),
  ADD KEY `activity_created` (`created`);
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id`),
  ADD KEY `menu_category` (`categoryId`);
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_orders_user` (`userId`),
  ADD KEY `fk_orders_status` (`statusId`),
  ADD KEY `orders_table` (`tableId`),
  ADD KEY `orders_reservation` (`reservationId`),
  ADD KEY `orders_waiter` (`waiterId`);
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_order_items_order` (`orderId`),
  ADD KEY `fk_order_items_dish` (`dishId`),
  ADD KEY `fk_order_items_status` (`statusId`);
ALTER TABLE `order_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_user` (`userId`),
  ADD KEY `reservation_table` (`tableId`),
  ADD KEY `reservation_status` (`statusId`);
ALTER TABLE `reservation_statuses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`);
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);
ALTER TABLE `cafe_tables`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `activity_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `menu`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `order_statuses`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `reservation_statuses`
  MODIFY `id` tinyint(3) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
ALTER TABLE `menu`
  ADD CONSTRAINT `fk_menu_category` FOREIGN KEY (`categoryId`) REFERENCES `categories` (`id`);
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `fk_activity_user` FOREIGN KEY (`userId`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_orders_reservation` FOREIGN KEY (`reservationId`) REFERENCES `reservations` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_orders_status` FOREIGN KEY (`statusId`) REFERENCES `order_statuses` (`id`),
  ADD CONSTRAINT `fk_orders_table` FOREIGN KEY (`tableId`) REFERENCES `cafe_tables` (`id`),
  ADD CONSTRAINT `fk_orders_user` FOREIGN KEY (`userId`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_orders_waiter` FOREIGN KEY (`waiterId`) REFERENCES `users` (`id`) ON DELETE SET NULL;
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_items_dish` FOREIGN KEY (`dishId`) REFERENCES `menu` (`id`),
  ADD CONSTRAINT `fk_order_items_order` FOREIGN KEY (`orderId`) REFERENCES `orders` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_items_status` FOREIGN KEY (`statusId`) REFERENCES `order_statuses` (`id`);
ALTER TABLE `reservations`
  ADD CONSTRAINT `fk_reservation_status` FOREIGN KEY (`statusId`) REFERENCES `reservation_statuses` (`id`),
  ADD CONSTRAINT `fk_reservation_table` FOREIGN KEY (`tableId`) REFERENCES `cafe_tables` (`id`),
  ADD CONSTRAINT `fk_reservation_user` FOREIGN KEY (`userId`) REFERENCES `users` (`id`);
COMMIT;
