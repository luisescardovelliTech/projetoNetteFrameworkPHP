CREATE TABLE tags (
                      idtags INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
                      nome VARCHAR(45) NULL,
                      PRIMARY KEY(idtags)
);

CREATE TABLE categoria (
                           idcategoria INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
                           nome VARCHAR(45) NULL,
                           PRIMARY KEY(idcategoria)
);

CREATE TABLE autores (
                         idautores INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
                         nome VARCHAR(50) NOT NULL,
                         email VARCHAR(45) NOT NULL,
                         celular VARCHAR(11) NULL,
                         PRIMARY KEY(idautores)
);

CREATE TABLE posts (
                       idposts INTEGER UNSIGNED NOT NULL AUTO_INCREMENT,
                       categoria_idcategoria INTEGER UNSIGNED NOT NULL,
                       autores_idautores INTEGER UNSIGNED NOT NULL,
                       titulo VARCHAR(30) NULL,
                       conteudo TEXT NULL,
                       url_imagem VARCHAR(50) NULL,
                       data_publicacao DATETIME NULL,
                       PRIMARY KEY(idposts),
                       INDEX posts_FKIndex1(autores_idautores),
                       INDEX posts_FKIndex2(categoria_idcategoria),
                       FOREIGN KEY(autores_idautores)
                           REFERENCES autores(idautores)
                           ON DELETE NO ACTION
                           ON UPDATE NO ACTION,
                       FOREIGN KEY(categoria_idcategoria)
                           REFERENCES categoria(idcategoria)
                           ON DELETE NO ACTION
                           ON UPDATE NO ACTION
);

CREATE TABLE tags_has_posts (
                                tags_idtags INTEGER UNSIGNED NOT NULL,
                                posts_idposts INTEGER UNSIGNED NOT NULL,
                                PRIMARY KEY(tags_idtags, posts_idposts),
                                INDEX tags_has_posts_FKIndex1(tags_idtags),
                                INDEX tags_has_posts_FKIndex2(posts_idposts),
                                FOREIGN KEY(tags_idtags)
                                    REFERENCES tags(idtags)
                                    ON DELETE NO ACTION
                                    ON UPDATE NO ACTION,
                                FOREIGN KEY(posts_idposts)
                                    REFERENCES posts(idposts)
                                    ON DELETE NO ACTION
                                    ON UPDATE NO ACTION
);

