DROP TABLE IF EXISTS `author`;
CREATE TABLE `author`
(
    `id`                 int(10) unsigned NOT NULL AUTO_INCREMENT,
    `full_name`          varchar(255) DEFAULT NULL,
    `publications_count` int(6) unsigned DEFAULT NULL,
    `pid`                int(10) unsigned DEFAULT NULL,
    `orcid`              varchar(20)  DEFAULT NULL,
    `rid`                varchar(20)  DEFAULT NULL,
    `photo`              text,
    `h_index`            float        DEFAULT '0',
    `times_cited`        int(10) unsigned DEFAULT '0',
    `citations_per_year` text,
    `publons_updated_at` datetime     DEFAULT NULL,
    `session_id`         int(10) unsigned DEFAULT NULL,
    `created_at`         datetime     DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `pid_UNIQUE` (`pid`),
    CONSTRAINT `author_session_id` FOREIGN KEY (`session_id`) REFERENCES `session` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `publication`;
CREATE TABLE `publication`
(
    `id`               int(10) unsigned NOT NULL AUTO_INCREMENT,
    `title`            text,
    `journal_title`    varchar(255) DEFAULT NULL,
    `published_at`     varchar(45)  DEFAULT NULL,
    `times_cited`      int(11) DEFAULT NULL,
    `identifier_name`  varchar(45)  DEFAULT NULL,
    `identifier_value` varchar(255) DEFAULT NULL,
    `author_id`        int(10) unsigned DEFAULT NULL,
    `session_id`       int(10) unsigned DEFAULT NULL,
    `created_at`       datetime     DEFAULT NULL,
    PRIMARY KEY (`id`),
    CONSTRAINT `publication_author_id` FOREIGN KEY (`author_id`) REFERENCES `author` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
    CONSTRAINT `publication_session_id` FOREIGN KEY (`session_id`) REFERENCES `session` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=225 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `session`;
CREATE TABLE `session`
(
    `id`    int(10) unsigned NOT NULL AUTO_INCREMENT,
    `token` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
