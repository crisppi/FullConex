import pandas as pd
from pandas.io import sql
from sqlalchemy import create_engine
from urllib.parse import quote_plus

# Connect to the database
engine = create_engine("mysql+mysqlconnector://diretoria2:%s@mdb-accert.mysql.uhserver.com/mydb_accert" % quote_plus("Guga@0401"))

# Test the connection
connection = engine.connect()

df = pd.read_csv("CID/CID-10-CATEGORIAS.CSV", sep=";", encoding="iso-8859-1")
df = df.rename({"CAT":"cat","DESCRICAO":"descricao"})
df = df.drop(columns=['DESCRABREV', 'REFER', 'EXCLUIDOS','Unnamed: 6','CLASSIF'])

df.to_sql('tb_cid', con=connection, if_exists='append', index=False)
print(df)