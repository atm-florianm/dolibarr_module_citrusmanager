-- Copyright (C) 2019 SuperAdmin
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


CREATE TABLE llx_citrusmanager_citrus(
	-- BEGIN MODULEBUILDER FIELDS
	rowid         INTEGER AUTO_INCREMENT PRIMARY KEY,

	-- reference, e.g. "femminelloIT", "starrubyFR", "starrubyES", "US119PT", …
	ref           VARCHAR(255),

    -- price (expressed in Dolibarr's pre-configured main currency)
	price         FLOAT,

	-- entity: ID of company if multi-company is enabled
	entity        INTEGER DEFAULT 1 NOT NULL,

	-- label, e.g. "Citrus limon “femminello” (Italia)", "Citrus ×paradisi “Star Ruby” (France, Corsica)", etc.
	label         VARCHAR(255),

	-- category, e.g. "sour", "sweet"
	fk_category   INTEGER,

	date_creation DATETIME NOT NULL,
	tms           TIMESTAMP NOT NULL,
	import_key    VARCHAR(14),

	-- user who created / last edited the citrus item
	fk_user_creat INTEGER,
	fk_user_modif INTEGER,

	INDEX idx_citrusmanager_citrus (ref),
	FOREIGN KEY (fk_category)   REFERENCES llx_c_citrus_category(rowid) ON DELETE CASCADE,
	FOREIGN KEY (fk_user_modif) REFERENCES llx_user(rowid) ON DELETE CASCADE,
	FOREIGN KEY (fk_user_creat) REFERENCES llx_user(rowid) ON DELETE CASCADE
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;

