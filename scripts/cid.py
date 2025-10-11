#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import sys
import pandas as pd
from sqlalchemy import create_engine, text
from urllib.parse import quote_plus

# =========================
# CONFIG CONEXÃO
# =========================
USER = "u650318666_diretoria10"
PASS = quote_plus("Fullcare12@")  # trata caracteres especiais
HOST = "2.59.150.2"
DB   = "u650318666_mydb_accert_ho"

# Driver recomendado no macOS (PyMySQL):
ENGINE_URL = f"mysql+pymysql://{USER}:{PASS}@{HOST}/{DB}"
engine = create_engine(ENGINE_URL, pool_pre_ping=True)

# =========================
# LOCALIZAÇÃO DO CSV
# - Aceita via argumento: python cid.py "/caminho/arquivo.csv"
# - Se não passar, tenta localizar automaticamente no projeto
# =========================
def resolve_csv_path() -> str:
    if len(sys.argv) > 1:
        return sys.argv[1]

    base_dir = os.path.dirname(os.path.abspath(__file__))
    # tenta ../CID/CID-10-SUBCATEGORIAS.CSV, depois mesma pasta
    candidates = [
        os.path.normpath(os.path.join(base_dir, "..", "scripts", "CID-10-SUBCATEGORIAS.CSV")),
        os.path.join(base_dir, "CID-10-SUBCATEGORIAS.CSV"),
    ]
    for p in candidates:
        if os.path.exists(p):
            return p
    raise FileNotFoundError(
        "CSV não encontrado. Informe o caminho como argumento ou coloque em ../CID/ ou na mesma pasta do script."
    )

# =========================
# LEITURA (apenas colunas necessárias)
# =========================
def load_csv(csv_path: str) -> pd.DataFrame:
    # usecols limita a leitura a CAT e DESCRICAO
    df = pd.read_csv(
        csv_path,
        sep=";",
        encoding="iso-8859-1",
        dtype=str,
        usecols=["SUBCAT", "DESCRICAO"]
    )
    # padroniza nomes e limpa
    df.columns = [c.strip().upper() for c in df.columns]  # garante CAT/DESCRICAO
    df = df.rename(columns={"SUBCAT": "cat", "DESCRICAO": "descricao"})
    df["cat"] = df["cat"].astype(str).str.strip()
    df["descricao"] = df["descricao"].astype(str).str.strip()

    # remove vazios e duplicados por cat
    df = df[df["cat"].notna() & (df["cat"] != "")]
    df = df.drop_duplicates(subset=["cat"], keep="first")
    return df

# =========================
# ESCRITA NO BANCO
# - if_exists='append' (seguro para tabelas com FK)
# - chunksize + method='multi' (performance)
# =========================
def write_to_db(df: pd.DataFrame, table: str = "tb_cid"):
    if df.empty:
        print("[INFO] DataFrame vazio, nada a inserir.")
        return
    with engine.begin() as conn:
        # Opcional: garantir que a tabela existe com estrutura mínima
        # (somente se você não tiver criado antes; comente se já existe com PK/FK)
        conn.execute(text(f"""
            CREATE TABLE IF NOT EXISTS `{table}` (
                id_cid INT AUTO_INCREMENT PRIMARY KEY,
                cat VARCHAR(16) NOT NULL,
                descricao VARCHAR(512) NOT NULL,
                UNIQUE KEY ux_{table}_cat (cat)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        """))
        df.to_sql(
            table,
            con=conn,
            if_exists="append",
            index=False,
            chunksize=2000,
            method="multi"
        )
    print(f"[OK] Inseridas {len(df)} linhas em {table}.")

# =========================
# MAIN
# =========================
def main():
    csv_path = resolve_csv_path()
    print(f"[INFO] Lendo CSV: {csv_path}")
    df = load_csv(csv_path)
    print(f"[INFO] Linhas após limpeza: {len(df)}")
    write_to_db(df, table="tb_cid")
    print("[INFO] Finalizado com sucesso. ✅")

if __name__ == "__main__":
    main()
