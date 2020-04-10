# SID-Grupo35
Repositório com os desenvolvimentos feitos para a cadeira de SID

Na pasta de Ficheiro temos o código que foi utilizado para colocar funcional a migração por ficheiro.
Temos 3 ficheiros de python e um ficheiro de batch. O script, em que no nosso caso é o ficheiro "main.py", deve ser usado com o Windows Task Scheduler e depois o ficheiro batch vai correr o script.
O ficheiro batch necessita da localização da aplicação do Python (python.exe), a localização do script (main.py) e a instrução pause.
A seguir usamos o task manager para configurar a periocidade da tarefa(task) com base no requisito.
Configurações ao nível do script devem ser atualizadas no ficheiro config.py, configurações essas que podem ser o :
-Número de tentativas (caso haja erro quantas vezes dizemos para tentar novamente, default 3);
-Credenciais da Base de Dados (utilizador, password, host, base de dados e a flag raise_on_warning, na qual se estiver a true informa se houver avisos);
-Tabelas para migrar (no nosso caso só a tabela de Log);
-Intervalo entre tentativas(quanto tempo esperar em caso de falha, default 5 minutos).
No ficheiro de utils.py podemos encontrar o código das funções corridas no script corrido no batch (main.py). Temos a tentativa de conexão à base de dados na função open_db_connection; buscar os campos que ainda foram migrado com o comando ("SELECT * FROM "+table+" WHERE Migrado = 0"); temos ainda a inserção dos dados na tabela com o comando ('INSERT INTO '+table+' VALUES (' + '"{0}"'.format('", "'.join(data)) + ')'). Finalmente temos comandos de ajuda para escrever os dados num ficheiro .csv e atualizar os dados a migrar (especialmente o campo Migrado). Estas funções são save_csv(para guardar no ficheiro .csv); insert_data(para inserir os dados na tabela) e finalmente o update_data (para dar update ao campo Migrado).

Na pasta PHP temos o script de migração (scriptMigracao.php) que serve para colocar funcional a migração por php.
A inicializar temos o nome do servidor, o nome de user, password e o nome da base de dados;
A ligação à bd de origem e ligação à base de dados de destino;
A criação das tabelas Log e Auditor;
Criação das Stored Procedures e Triggers do auditor;
Adicionar um utilizador com a Role Auditor;
A inserção dos valores migrados da base de dados de origem para a de destino com a ajuda do campo Migrado. Comando:
"INSERT INTO log (IdLog,Utilizador,Tabela,IdTabela,Operacao,ValorAntigo,ValorNovo,Campo,timestamp,Migrado)SELECT idLog,Utilizador,Tabela,IdTabela,Operacao,ValorAntigo,ValorNovo,Campo,timestamp,Migrado FROM teste.log WHERE Migrado = '0'";
Finalmente fechamos as ligações.