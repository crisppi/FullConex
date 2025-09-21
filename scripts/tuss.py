import mysql.connector
import pandas as pd

# Configurações de conexão com o banco de dados
config = {
    'user': 'diretoria2',
    'password': 'Guga@0401',
    'host': 'mdb-accert.mysql.uhserver.com',
    'database': 'mydb_accert',
}

# Conectando ao banco de dados
conn = mysql.connector.connect(**config)

# Criando um cursor para executar consultas SQL
cursor = conn.cursor()

# Carregue o arquivo
# uploaded = files.upload()

# Leia o arquivo com Pandas
df = pd.read_csv('correlacaotuss-rol_2021_site.csv', sep=';')

columns = ['cod_tuss, terminologia_tuss, roll_tuss, subgrupo_tuss, grupo_tuss']
selected_columns = df[['Código', 'Terminologia de Procedimentos e Eventos em Saúde (Tab. 22)','ROL ANS Resolução Normativa nº 465/2021       ','SUBGRUPO','GRUPO']]
placeholders = ', '.join(['%s'] * len(selected_columns.columns))
print(selected_columns.head())
# Montar a consulta SQL de inserção
query = f"INSERT INTO tb_tuss_ans (cod_tuss, terminologia_tuss, roll_tuss, subgrupo_tuss, grupo_tuss) VALUES (%s, %s, %s, %s, %s)"
print(query)
# Executar a consulta para inserir os dados
cursor = conn.cursor()
for index, row in selected_columns.iterrows():
    cursor.execute(query, tuple(row))

conn.commit()
print("finalizado!!!!")
