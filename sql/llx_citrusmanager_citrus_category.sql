-- Copyright (C) 2019 Florian Mortgat
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see http://www.gnu.org/licenses/.

CREATE TABLE llx_citrusmanager_citrus_category(
	rowid                   INTEGER AUTO_INCREMENT PRIMARY KEY,
	fk_c_citrus_category    INTEGER,
	fk_citrusmanager_citrus INTEGER,

    FOREIGN KEY (fk_c_citrus_category) REFERENCES llx_c_citrus_category(rowid) ON DELETE CASCADE,
    FOREIGN KEY (fk_citrusmanager_citrus) REFERENCES llx_citrusmanager_citrus(rowid) ON DELETE CASCADE,
    UNIQUE KEY (fk_c_citrus_category, fk_citrusmanager_citrus)
) ENGINE=innodb;