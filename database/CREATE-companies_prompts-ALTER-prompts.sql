-- Creacion de tabla relacion Companies-prompt
CREATE TABLE companies_prompts(
    prompts_id INT NOT NULL,
    companies_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY(prompts_id, companies_id),
    FOREIGN KEY(prompts_id) REFERENCES prompts(id),
    FOREIGN KEY(companies_id) REFERENCES companies(id)
);

-- Adicion columna basic-> Prompt por defecto (No puede ser eliminado por el usuario)
ALTER TABLE prompts ADD COLUMN basic BOOLEAN DEFAULT 0 NOT NULL;

-- ------------------------------------------------------------------------------------------------------

-- Generar los inserts pendientes con basicos TRUE a la companies_id=1 || Cambiar companies_id
-- INSERT INTO companies_prompts(companies_id,prompts_id) SELECT 1,prompts.id FROM prompts where prompts.basic=true AND id not in (select companies_prompts.prompts_id from companies_prompts where companies_prompts.companies_id=1);

-- Generar los inserts pendientes con basicos TRUE en todas las companies



-- WARNING: Eliminar del registro de prompts básicos TRUE de la companies_id=1
-- DELETE FROM companies_prompts WHERE companies_prompts.companies_id=1 AND companies_prompts.prompts_id IN (SELECT prompts.id FROM prompts where prompts.basic = true);

-- WARNING: Eliminar TODOS los registros de prompts básicos TRUE de las companies
-- DELETE FROM companies_prompts WHERE companies_prompts.prompts_id IN (SELECT prompts.id FROM prompts where prompts.basic = true);