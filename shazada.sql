-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 29, 2025 at 03:02 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `shazada`
--

-- --------------------------------------------------------

--
-- Table structure for table `concern_orders`
--

CREATE TABLE `concern_orders` (
  `concern_order_id` int(11) NOT NULL,
  `concern_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_concerns`
--

CREATE TABLE `customer_concerns` (
  `concern_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `admin_reply` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_concerns`
--

INSERT INTO `customer_concerns` (`concern_id`, `user_id`, `message`, `admin_reply`, `created_at`, `updated_at`) VALUES
(9, 5, 'man, Im so drained from work. I swear, I\'m starting to talk to myself at this point. \r\nI mean, not like this is gonna matter, this is just my copy of the site for testing purposes', NULL, '2025-03-29 01:02:21', '2025-03-29 01:02:21'),
(10, 5, 'yeeah… shazada is like 80% done the shopping system\'s coming together, just need to polish the payments modules and other stuff', NULL, '2025-03-29 01:03:25', '2025-03-29 01:03:25'),
(11, 5, 'Hopefully we\'ll go live soon. I just gotta keep pushing updates to my ollie_ftp server. Pretty neat setup, makes my life easier.', NULL, '2025-03-29 01:04:07', '2025-03-29 01:04:07'),
(12, 5, 'random thing tho, i found this USB stick just lying around. plugged it in out of curiosity. nothing crazy on it, just a few weird files lol', NULL, '2025-03-29 01:04:47', '2025-03-29 01:04:47'),
(13, 5, 'my computer acted a little weird, but nothing too bad. its just the work computer lol, just Maybe I couldve gone lucky and there\'s a lost Bitcoin wallet on it, but oh well', NULL, '2025-03-29 01:06:04', '2025-03-29 01:06:04'),
(14, 5, 'back to work. Need to finish uploading assets before I call it a night. (its morning)', NULL, '2025-03-29 01:06:36', '2025-03-29 01:06:36'),
(15, 4, 'in case i forgot, superadmin: adminpass', NULL, '2025-03-29 01:28:20', '2025-03-29 01:28:20'),
(16, 4, 'gnorw sgnihtemos', NULL, '2025-03-29 02:00:36', '2025-03-29 02:00:36');

-- --------------------------------------------------------

--
-- Table structure for table `delivery_addresses`
--

CREATE TABLE `delivery_addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `country` varchar(50) NOT NULL,
  `province` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `home_address` text NOT NULL,
  `zip_code` varchar(10) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `logistics_partners`
--

CREATE TABLE `logistics_partners` (
  `logistics_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `shipping_fee` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('To be packed','Paid','Packed and Shipped','Received by Logistics','Out for Delivery','Delivered') NOT NULL,
  `payment_method` enum('COD','Online Payment') NOT NULL,
  `logistics_partner` varchar(100) DEFAULT NULL,
  `delivery_address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_methods`
--

CREATE TABLE `payment_methods` (
  `payment_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `bank_account` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `images` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`images`)),
  `main_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `price`, `stock`, `images`, `main_image`, `created_at`) VALUES
(7, 'Heart necklace', 'a hold off the neck', 900.00, 3, '[\"..\\/assets\\/images\\/products\\/H.jpg\"]', '../assets/images/products/H.jpg', '2025-03-28 18:13:08'),
(8, 'Eclipse watch', 'only ticks when you\'re running out of time.', 4321.00, 1, '[\"..\\/assets\\/images\\/products\\/E.jpg\"]', '../assets/images/products/E.jpg', '2025-03-28 18:15:51'),
(9, 'Lifeline rope', 'An essential tool when you have no way out.', 1234.00, 6, '[\"..\\/assets\\/images\\/products\\/L.jpg\"]', '../assets/images/products/L.jpg', '2025-03-28 18:16:45'),
(10, 'Prison KEY replica', 'Limited edition—just like some people\'s freedom. ', 7654321.00, 8, '[\"..\\/assets\\/images\\/products\\/P.jpg\"]', '../assets/images/products/P.jpg', '2025-03-28 18:17:22');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` enum('COD','Online Payment') NOT NULL,
  `status` enum('Paid','Pending') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(32) NOT NULL,
  `email` varchar(100) NOT NULL,
  `user_type` enum('admin','buyer') NOT NULL,
  `verification_key` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `user_type`, `verification_key`, `created_at`) VALUES
(1, 'superadmin', '25e4ee4e9229397b6b17776bfceaf8e7', 'eilrach@mailstr.com', 'admin', '31141524', '2025-03-08 18:44:04'),
(4, 'ollie', 'fcea920f7412b5da7be0cf42b8c93759', 'ollie73@techfort.com', 'buyer', NULL, '2025-03-28 18:14:03'),
(5, 'ollie73', '29c3eea3f305d6b823f562ac4be35217', 'ollie73@mailstr.com', 'buyer', NULL, '2025-03-29 00:54:14');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `concern_orders`
--
ALTER TABLE `concern_orders`
  ADD PRIMARY KEY (`concern_order_id`),
  ADD KEY `concern_id` (`concern_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `customer_concerns`
--
ALTER TABLE `customer_concerns`
  ADD PRIMARY KEY (`concern_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `delivery_addresses`
--
ALTER TABLE `delivery_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `logistics_partners`
--
ALTER TABLE `logistics_partners`
  ADD PRIMARY KEY (`logistics_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `concern_orders`
--
ALTER TABLE `concern_orders`
  MODIFY `concern_order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customer_concerns`
--
ALTER TABLE `customer_concerns`
  MODIFY `concern_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `delivery_addresses`
--
ALTER TABLE `delivery_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `logistics_partners`
--
ALTER TABLE `logistics_partners`
  MODIFY `logistics_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payment_methods`
--
ALTER TABLE `payment_methods`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `concern_orders`
--
ALTER TABLE `concern_orders`
  ADD CONSTRAINT `concern_orders_ibfk_1` FOREIGN KEY (`concern_id`) REFERENCES `customer_concerns` (`concern_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `concern_orders_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `customer_concerns`
--
ALTER TABLE `customer_concerns`
  ADD CONSTRAINT `customer_concerns_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `delivery_addresses`
--
ALTER TABLE `delivery_addresses`
  ADD CONSTRAINT `delivery_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_methods`
--
ALTER TABLE `payment_methods`
  ADD CONSTRAINT `payment_methods_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
