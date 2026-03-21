Portfolio System

## USERS

id (PK)  
email (unique)  
password  
created_at  
updated_at  

---

## perfiles_usuario

id (PK)  
user_id  
nombre  
apellido  
universidad  
ubicacion  
foto_perfil  
created_at  
updated_at  

---
## portfolios

id (PK)  
user_id  
titulo  
biografia  
slug (unique)  
is_public  
template  
created_at  
updated_at  

---
## habilidades

id (PK)  
portfolio_id  
nombre  
tipo (technical | soft)  
nivel (1-5)  
created_at  
updated_at  
---

##  proyectos

id (PK)  
portfolio_id  
titulo  
descripcion  
tecnologias (text)  
github_url  
demo_url  
created_at  
updated_at  

---

## project_media

id (PK)  
project_id  
file_url  
tipo (image | video)  
created_at  

---
## experiencias

id (PK)  
portfolio_id  
tipo (work | education)  
titulo  
institucion  
descripcion  
fecha_inicio  
fecha_fin  
created_at  

---
## social_links

id (PK)  
portfolio_id  
plataforma (github | linkedin | other)  
url  
created_at  

---
## configuracion_visibilidad

id (PK)  
portfolio_id  
mostrar_proyectos  
mostrar_habilidades  
mostrar_experiencia  
mostrar_redes  
created_at  

---
## portfolio_templates

id (PK)  
nombre  
tema (dark | light)  
created_at  

---
## exportaciones

id (PK)  
portfolio_id  
file_url  
created_at  
