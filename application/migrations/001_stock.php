<?php

defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Stock extends CI_Migration {

        public function up()
        {
                $this->dbforge->add_field(array(
                        'user_seq' => array(
                                'type' => 'BIGINT',
                                'constraint' => 20,
                                'unsigned' => TRUE,
                                'auto_increment' => TRUE
                        ),
                        'user_email' => array(
                                'type' => 'VARCHAR',
                                'constraint' => '45',
                                'null' => FALSE,
                        ),
                        'user_name' => array(
                                'type' => 'VARCHAR',
                                'constraint' => '45',
                                'null' => FALSE,
                        ),
                        'user_password' => array(
                                'type' => 'VARCHAR',
                                'constraint' => '255',
                                'null' => FALSE,
                        ),
                        'reg_date' => array(
                                'type' => 'DATETIME',
                                'null' => FALSE,
                                'DEFAULT' => 'CURRENT_TIMESTAMP'
                        ),
                ));
                $this->dbforge->add_key('user_seq', TRUE); //PRIMARY KEY
                //$this->dbforge->add_key('user_seq'); //KEY INDEX
                $this->dbforge->create_table('user');

                /*
                CREATE TABLE `user` (
                  `user_seq` bigint(20) NOT NULL AUTO_INCREMENT,
                  `user_email` varchar(45) NOT NULL,
                  `user_name` varchar(45) NOT NULL,
                  `user_password` varchar(255) NOT NULL,
                  `reg_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`user_seq`)
                ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8

                CREATE TABLE `order_BTC` (
                  `od_seq` bigint(20) NOT NULL AUTO_INCREMENT,
                  `user_seq` bigint(20) NOT NULL,
                  `coin_code` varchar(45) NOT NULL,
                  `base_code` varchar(45) NOT NULL,
                  `trade_code` varchar(10) NOT NULL,
                  `od_status` varchar(10) NOT NULL DEFAULT '01' COMMENT '01:신규, 02:부분체결, 03:전체체결, 04:취소',
                  `ord_price` decimal(65,8) NOT NULL,
                  `ord_qty` decimal(65,8) NOT NULL,
                  `unexe_qty` decimal(65,8) NOT NULL,
                  `update_date` datetime DEFAULT CURRENT_TIMESTAMP,
                  `reg_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`od_seq`)
                ) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8

                CREATE TABLE `trade_BTC` (
                  `trd_seq` bigint(20) NOT NULL AUTO_INCREMENT,
                  `od_seq` bigint(20) NOT NULL,
                  `trade_code` varchar(10) NOT NULL,
                  `fuser_seq` bigint(20) NOT NULL,
                  `tuser_seq` bigint(20) NOT NULL,
                  `coin_code` varchar(45) NOT NULL,
                  `base_code` varchar(45) NOT NULL,
                  `price` decimal(65,8) NOT NULL,
                  `qty` decimal(65,8) NOT NULL,
                  `reg_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`trd_seq`)
                ) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8

                CREATE TABLE `wallet_BTC` (
                  `wl_seq` bigint(20) NOT NULL AUTO_INCREMENT,
                  `user_seq` bigint(20) NOT NULL,
                  `base` decimal(65,8) DEFAULT '0.00000000',
                  `trade_base` decimal(65,8) DEFAULT '0.00000000',
                  `udpate_date` datetime DEFAULT CURRENT_TIMESTAMP,
                  `reg_date` datetime DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`wl_seq`)
                ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8

                CREATE TABLE `wallet_KRW` (
                  `wl_seq` bigint(20) NOT NULL AUTO_INCREMENT,
                  `user_seq` bigint(20) NOT NULL,
                  `base` decimal(65,8) DEFAULT '0.00000000',
                  `trade_base` decimal(65,8) DEFAULT '0.00000000',
                  `udpate_date` datetime DEFAULT CURRENT_TIMESTAMP,
                  `reg_date` datetime DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`wl_seq`)
                ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8
                */
        }

        public function down()
        {
                $this->dbforge->drop_table('blog');
        }
}
