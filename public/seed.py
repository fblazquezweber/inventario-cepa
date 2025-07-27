#!/usr/bin/env python3
"""
seed.py – carga datos falsos en inventario_ocana.db
"""

# ───── IMPORTS (no cambies el orden) ───────────────────────────
import argparse
import random
import sqlite3
import os
from faker import Faker
# ───────────────────────────────────────────────────────────────

# ───── NUEVO: ruta y función para leer los usuarios reales ─────
USERS_DB = os.path.abspath("..\\src\\database\\usuarios_ocana.db")

def get_user_ids() -> list[int]:
    """Devuelve la lista de id_usuario presentes en usuarios_ocana.db."""
    if not os.path.exists(USERS_DB):
        raise RuntimeError(f"No se encontró {USERS_DB}")
    con_u = sqlite3.connect(USERS_DB)
    ids = [row[0] for row in con_u.execute("SELECT id_usuario FROM Usuarios")]
    con_u.close()
    if not ids:
        raise RuntimeError("usuarios_ocana.db está vacío: crea al menos un usuario")
    return ids
# ───────────────────────────────────────────────────────────────
ids_usuarios: list[int] = []          # será rellena en main()
fake, rnd = Faker("es_ES"), random.Random()

# … aquí continúa tu SCHEMA = """ … """ y el resto del script


# ► Listas tomadas del controlador PHP
CATEGORIAS = [
    "AUDIOVISUALES", "INFORMÁTICA", "MOBILIARIO",
    "HERRAMIENTAS", "LABORATORIO", "LIBROS", "OTROS"
]
ESTADOS = ["OPERATIVO", "MANTENIMIENTO", "PRÉSTAMO", "INACTIVO", "BAJA"]

# ════════════════════════════════════════════════════════════════════════
SCHEMA = """
CREATE TABLE IF NOT EXISTS inventario (
  id_objeto INTEGER PRIMARY KEY AUTOINCREMENT,
  nombre_objeto     VARCHAR(255) NOT NULL,
  descripcion       TEXT,
  categoria         VARCHAR(100),
  marca             VARCHAR(100),
  modelo            VARCHAR(100),
  numero_serie      VARCHAR(100) NOT NULL UNIQUE,
  codigo_interno    VARCHAR(100) NOT NULL UNIQUE,
  fecha_adquisicion DATE,
  valor_adquisicion DECIMAL(10,2),
  estado            VARCHAR(50) DEFAULT 'OPERATIVO',
  ubicacion         VARCHAR(255),
  observacion       TEXT,
  usuario_creacion  INT,
  fecha_creacion    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS movimientos_inventario (
  id_movimiento INTEGER PRIMARY KEY AUTOINCREMENT,
  id_objeto     INTEGER NOT NULL,
  id_usuario    INTEGER NOT NULL,
  tipo_movimiento  VARCHAR(50) NOT NULL,
  fecha_movimiento DATETIME DEFAULT CURRENT_TIMESTAMP,
  motivo        TEXT,
  FOREIGN KEY (id_objeto) REFERENCES inventario(id_objeto)
);

CREATE TABLE IF NOT EXISTS historial_actualizaciones (
  id_historial INTEGER PRIMARY KEY AUTOINCREMENT,
  id_objeto    INTEGER NOT NULL,
  id_usuario   INTEGER NOT NULL,
  campo_modificado TEXT NOT NULL,
  valor_anterior   TEXT,
  valor_nuevo      TEXT NOT NULL,
  fecha_modificacion DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_objeto) REFERENCES inventario(id_objeto)
);
"""
# ════════════════════════════════════════════════════════════════════════
def insertar_objetos(cur, n):
    ids = []
    for _ in range(n):
        cat     = rnd.choice(CATEGORIAS)
        observ  = fake.sentence(nb_words=3)
        usr_id  = rnd.choice(ids_usuarios)

        cur.execute("""
        INSERT INTO inventario
        (nombre_objeto, descripcion, categoria, marca, modelo,
         numero_serie, codigo_interno, fecha_adquisicion, valor_adquisicion,
         estado, ubicacion, observacion, usuario_creacion)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)""", (
            fake.word().upper(),
            fake.sentence(nb_words=4),
            cat,
            fake.company()[:100].upper(),
            fake.bothify("??###").upper(),
            fake.unique.bothify("??######").upper(),
            f"{cat[:3]}-{fake.unique.random_int(1,9999):04d}",
            fake.date_between("-6y", "today"),
            rnd.uniform(50, 5000),
            "OPERATIVO",
            fake.city().upper(),
            observ,          # ← AQUÍ: sustituyes "" por observ
            usr_id
        ))
        ids.append(cur.lastrowid)
    return ids


def insertar_mov_ingreso(cur, ids):
    for oid in ids:
        cur.execute("""
        INSERT INTO movimientos_inventario
        (id_objeto, id_usuario, tipo_movimiento, motivo)
        VALUES (?,?,?,?)""",
        (oid,
         rnd.choice(ids_usuarios),           # ID real
         "Ingreso",
         "Objeto registrado en el inventario."))


def insertar_historial(cur, ids, por_obj):
    campos = ["nombre_objeto", "numero_serie", "codigo_interno"]
    for oid in ids:
        for _ in range(por_obj):
            campo = rnd.choice(campos)
            val_anterior = fake.word().upper() if campo == "nombre_objeto" else \
                           fake.unique.bothify("??######").upper()
            val_nuevo    = fake.word().upper() if campo == "nombre_objeto" else \
                           fake.unique.bothify("??######").upper()
            cur.execute("""
            INSERT INTO historial_actualizaciones
            (id_objeto, id_usuario, campo_modificado,
             valor_anterior, valor_nuevo)
            VALUES (?,?,?,?,?)""",
            (oid,
             rnd.choice(ids_usuarios),        # ID real
             campo, val_anterior, val_nuevo))



def main():
    ap = argparse.ArgumentParser()
    ap.add_argument("--db", required=True, help="Ruta al .db (con extensión)")
    ap.add_argument("--objetos", type=int, default=50)
    ap.add_argument("--hist", type=int, default=0, help="Cambios de historial por objeto")
    ap.add_argument("--truncate", action="store_true", help="Vacía tablas antes de insertar")
    args = ap.parse_args()


    # ───── AQUI: obtenemos los ids de usuario ─────
    global ids_usuarios          # ← ① declara que usarás la var. global
    ids_usuarios = get_user_ids()  # ← ② carga [1, 2, …] desde usuarios_ocana.db
    # ──────────────────────────────────────────────


    # ► Asegura ruta absoluta
    db_path = os.path.abspath(args.db)
    os.makedirs(os.path.dirname(db_path), exist_ok=True)

    con = sqlite3.connect(db_path)
    con.execute("PRAGMA foreign_keys = ON")
    cur = con.cursor()
    cur.executescript(SCHEMA)

    if args.truncate:
        cur.executescript("""
            DELETE FROM historial_actualizaciones;
            DELETE FROM movimientos_inventario;
            DELETE FROM inventario;
            VACUUM;""")

    con.execute("BEGIN")
    ids = insertar_objetos(cur, args.objetos)
    insertar_mov_ingreso(cur, ids)
    if args.hist > 0:
        insertar_historial(cur, ids, args.hist)
    con.commit()

    print(f"✔ {len(ids)} objetos | "
          f"{len(ids)} movimientos 'Ingreso' | "
          f"{len(ids)*args.hist} historiales → {db_path}")


if __name__ == "__main__":
    main()
