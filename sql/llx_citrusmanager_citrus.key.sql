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

CREATE INDEX IF NOT EXISTS idx_citrusmanager_citrus_rowid ON llx_citrusmanager_citrus (rowid);
CREATE INDEX IF NOT EXISTS idx_citrusmanager_citrus_ref ON llx_citrusmanager_citrus (ref);

SET FOREIGN_KEY_CHECKS = 0;

DELIMITER //
-- foreign key fk_product
IF NOT EXISTS (
    SELECT NULL
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE
        CONSTRAINT_SCHEMA = DATABASE() AND
        CONSTRAINT_NAME = 'fk_citrusmanager_citrus_product' AND
        CONSTRAINT_TYPE = 'FOREIGN KEY'
)
THEN
    ALTER TABLE llx_citrusmanager_citrus
        ADD CONSTRAINT fk_citrusmanager_citrus_product
        FOREIGN KEY (fk_product)
        REFERENCES llx_product (rowid)
        ON DELETE SET NULL;
END IF

//

-- foreign key fk_category
IF NOT EXISTS (
    SELECT NULL
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE
        CONSTRAINT_SCHEMA = DATABASE() AND
        CONSTRAINT_NAME = 'fk_citrusmanager_citrus_category' AND
        CONSTRAINT_TYPE = 'FOREIGN KEY'
)
THEN
    ALTER TABLE llx_citrusmanager_citrus
        ADD CONSTRAINT fk_citrusmanager_citrus_category
        FOREIGN KEY (fk_category)
        REFERENCES llx_c_citrus_category (rowid)
        ON DELETE SET NULL;
END IF

//

-- foreign key fk_user_creat
IF NOT EXISTS (
    SELECT NULL
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE
        CONSTRAINT_SCHEMA = DATABASE() AND
        CONSTRAINT_NAME = 'fk_citrusmanager_citrus_user_creat' AND
        CONSTRAINT_TYPE = 'FOREIGN KEY'
)
THEN
    ALTER TABLE llx_citrusmanager_citrus
        ADD CONSTRAINT fk_citrusmanager_citrus_user_creat
        FOREIGN KEY (fk_user_creat)
        REFERENCES llx_user (rowid)
        ON DELETE SET NULL;
END IF

//

-- foreign key fk_user_modif
IF NOT EXISTS (
    SELECT NULL
    FROM information_schema.TABLE_CONSTRAINTS
    WHERE
        CONSTRAINT_SCHEMA = DATABASE() AND
        CONSTRAINT_NAME = 'fk_citrusmanager_citrus_user_modif' AND
        CONSTRAINT_TYPE = 'FOREIGN KEY'
)
THEN
    ALTER TABLE llx_citrusmanager_citrus
        ADD CONSTRAINT fk_citrusmanager_citrus_user_modif
        FOREIGN KEY (fk_user_modif)
        REFERENCES llx_user (rowid)
        ON DELETE SET NULL;
END IF

//

DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;
