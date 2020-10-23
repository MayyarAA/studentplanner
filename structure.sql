SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `board` (
  `boardID` int NOT NULL,
  `boardTitle` varchar(255) NOT NULL,
  `u.WATIAM` varchar(255) NOT NULL,
  `boardDateCreated` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `course` (
  `courseID` varchar(255) NOT NULL,
  `session` varchar(255) NOT NULL,
  `courseTitle` varchar(255) NOT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `share` (
  `b.boardID` int NOT NULL,
  `u.WATIAM` varchar(255) NOT NULL,
  `permission` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `task` (
  `taskID` int NOT NULL,
  `taskTitle` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `dueDate` int NOT NULL,
  `taskDateCreated` int NOT NULL,
  `importance` int NOT NULL,
  `typeOfWork` varchar(255) NOT NULL,
  `c.courseID` varchar(255) NOT NULL,
  `tl.listID` int NOT NULL,
  `archived` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `taskList` (
  `listID` int NOT NULL,
  `listTitle` varchar(255) NOT NULL,
  `boardID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE TABLE `user` (
  `WATIAM` varchar(255) NOT NULL,
  `firstName` varchar(255) NOT NULL,
  `lastName` varchar(255) NOT NULL,
  `program` varchar(255) NOT NULL,
  `passwordHash` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


ALTER TABLE `board`
  ADD PRIMARY KEY (`boardID`),
  ADD KEY `u.WATIAM` (`u.WATIAM`);

ALTER TABLE `course`
  ADD PRIMARY KEY (`courseID`,`session`);

ALTER TABLE `share`
  ADD PRIMARY KEY (`b.boardID`,`u.WATIAM`),
  ADD KEY `u.WATIAM` (`u.WATIAM`);

ALTER TABLE `task`
  ADD PRIMARY KEY (`taskID`),
  ADD KEY `c.courseID` (`c.courseID`),
  ADD KEY `tl.listID` (`tl.listID`);

ALTER TABLE `taskList`
  ADD PRIMARY KEY (`listID`),
  ADD KEY `board to list` (`boardID`);

ALTER TABLE `user`
  ADD PRIMARY KEY (`WATIAM`);


ALTER TABLE `board`
  MODIFY `boardID` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `task`
  MODIFY `taskID` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `taskList`
  MODIFY `listID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;


ALTER TABLE `board`
  ADD CONSTRAINT `board_ibfk_1` FOREIGN KEY (`u.WATIAM`) REFERENCES `user` (`WATIAM`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `share`
  ADD CONSTRAINT `share_ibfk_1` FOREIGN KEY (`b.boardID`) REFERENCES `board` (`boardID`),
  ADD CONSTRAINT `share_ibfk_2` FOREIGN KEY (`u.WATIAM`) REFERENCES `user` (`WATIAM`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `task`
  ADD CONSTRAINT `task_ibfk_1` FOREIGN KEY (`c.courseID`) REFERENCES `course` (`courseID`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  ADD CONSTRAINT `task_ibfk_2` FOREIGN KEY (`tl.listID`) REFERENCES `taskList` (`listID`) ON DELETE RESTRICT ON UPDATE RESTRICT;

ALTER TABLE `taskList`
  ADD CONSTRAINT `board to list` FOREIGN KEY (`boardID`) REFERENCES `board` (`boardID`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
