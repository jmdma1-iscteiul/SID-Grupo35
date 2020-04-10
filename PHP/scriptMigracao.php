<?php
//$time_start = microtime(true);
set_time_limit(10);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "teste";

// Conecccao com a base de dados ORIGEM
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Criacao da base de dados de DESTINO
$sql = "CREATE DATABASE IF NOT EXISTS newDB";
if (mysqli_query($conn, $sql)) {
    echo "Database created successfully with the name newDB" . "<br>";
} else {
    echo "Error creating database: " . mysqli_error($conn);
}

// Nova connecao para a base de dados de DESTINO
$dbnamee = "newDB";
$conne = new mysqli($servername, $username, $password, $dbnamee);
if ($conne->connect_error) {
    die("Connection failed: " . $conne->connect_error);
}

// Criacao das Tabelas LOG e AUDITOR
$sql = "CREATE TABLE IF NOT EXISTS log(
idLog INT(11) NOT NULL,
Utilizador VARCHAR(50) NOT NULL,
Tabela VARCHAR(50) NOT NULL,
IdTabela VARCHAR(50) NOT NULL,
Operacao VARCHAR(1) NOT NULL,
ValorAntigo VARCHAR(50),
ValorNovo VARCHAR(50),
Campo VARCHAR(50) NOT NULL,
timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
Migrado TINYINT(1) NOT NULL
)";
if ($conne->query($sql) === TRUE) {
    echo "Table Log created successfully" . "<br>";
} else {
    echo "Error creating table: " . $conne->error;
}

$sql = "CREATE TABLE IF NOT EXISTS Auditor(
Auditor_email VARCHAR(50) NOT NULL,
NomeAuditor VARCHAR(50) NOT NULL
)";
if ($conne->query($sql) === TRUE) {
    echo "Table Auditor created successfully" . "<br>";
} else {
    echo "Error creating table: " . $conne->error;
}

//Criacao das SP's e Trigger do auditor
if (!$conne->query("DROP PROCEDURE IF EXISTS Consulta_Datas") ||
    !$conne->query('CREATE PROCEDURE Consulta_Datas(IN `DataInicio` TIMESTAMP, IN `DataFim` TIMESTAMP) BEGIN SELECT * from log where log.timestamp>DataInicio AND log.timestamp<DataFim; END;')) {
    echo "Stored procedure creation failed: (" . $conne->errno . ") " . $conne->error;
	}
	
if (!$conne->query("DROP PROCEDURE IF EXISTS Consulta_Tabela") ||
    !$conne->query('CREATE PROCEDURE Consulta_Tabela(IN `tabela` VARCHAR(50)) BEGIN SELECT * FROM log where log.Tabela=tabela; END;')) {
    echo "Stored procedure creation failed: (" . $conne->errno . ") " . $conne->error;
	}
	
if (!$conne->query("DROP PROCEDURE IF EXISTS Consulta_Operacao") ||
    !$conne->query('CREATE PROCEDURE Consulta_Operacao(IN `operacao` VARCHAR(1)) BEGIN SELECT * from log where log.Operacao=operacao; END;')) {
    echo "Stored procedure creation failed: (" . $conne->errno . ") " . $conne->error;
	}
	
if (!$conne->query("DROP PROCEDURE IF EXISTS Consulta_Geral") ||
    !$conne->query('CREATE PROCEDURE Consulta_Geral() BEGIN SELECT * from log ORDER BY log.timestamp DESC; END;')) {
    echo "Stored procedure creation failed: (" . $conne->errno . ") " . $conne->error;
	}
	
//Adicionar User: Auditor
$sql1 = "SELECT emailAuditor FROM auditor";
$result1 = $conn->query($sql1);
if ($result1->num_rows > 0) {
	while($row1 = $result1->fetch_assoc()) {
		$sql2 = "SELECT Auditor_email FROM Auditor";
		$result2 = $conne->query($sql2);
		if ($result2->num_rows == 0) {
				$sql = "INSERT INTO Auditor (Auditor_email,NomeAuditor)
				SELECT emailAuditor,NomeAuditor FROM teste.auditor";
				if ($conne->query($sql) === TRUE) {
					echo "Adicionado o user:auditor à tabela" . "<br>";
				} else {
					echo "Error: " . $sql . "<br>" . $conne->error;
				}
				
				// Criacao do Auditor em si e permissoes
				$sql = "CREATE ROLE Auditor";
				if ($conne->query($sql) === TRUE) {
					echo "Created ROLE - Auditor" . "<br>";
				}
				
				$sql = "CREATE USER 'auditor'@'localhost' IDENTIFIED BY 'auditor123';";
				if ($conne->query($sql) === TRUE) {
					echo "New User created successfully - User: AUDITOR" . "<br>";
				}
				
				$sql = "GRANT SELECT ON newdb.log TO Auditor";
				if ($conne->query($sql) === TRUE) {
					echo "Permissao SELECT do Auditor" . "<br>";
				}
				
				$sql = "GRANT EXECUTE ON PROCEDURE newdb.Consulta_Datas TO Auditor";
				if ($conne->query($sql) === TRUE){
					echo "New User has permissions - EXECUTE on Consulta_Datas" . "<br>";
				}
				
				$sql = "GRANT EXECUTE ON PROCEDURE newdb.Consulta_Operacao TO Auditor";
				if ($conne->query($sql) === TRUE){
					echo "New User has permissions - EXECUTE on Consulta_Operacao" . "<br>";
				}
				
				$sql = "GRANT EXECUTE ON PROCEDURE newdb.Consulta_Tabela TO Auditor";
				if ($conne->query($sql) === TRUE){
					echo "New User has permissions - EXECUTE on Consulta_Tabela" . "<br>";
				}
				
				$sql = "GRANT EXECUTE ON PROCEDURE newdb.Consulta_Geral TO Auditor";
				if ($conne->query($sql) === TRUE){
					echo "New User has permissions - EXECUTE on Consulta_Geral" . "<br>";
				}
				
				$sql = "GRANT Auditor TO 'auditor'@'localhost'";
				if ($conne->query($sql) === TRUE) {
					echo "Granting auditorRole to user: auditor@localhost" . "<br>";
				}
				
			}
		}
    }

// Inserir os valores migrados da Base de dados de ORIGEM para a de DESTINO
$sql = "INSERT INTO log (IdLog,Utilizador,Tabela,IdTabela,Operacao,ValorAntigo,ValorNovo,Campo,timestamp,Migrado)
SELECT idLog,Utilizador,Tabela,IdTabela,Operacao,ValorAntigo,ValorNovo,Campo,timestamp,Migrado FROM teste.log WHERE Migrado = '0'";
if ($conne->query($sql) === TRUE) {
    echo "New record created successfully - LOG ADDED" . "<br>";
} else {
    echo "Error: " . $sql . "<br>" . $conne->error;
}

$conn->close();
$conne->close();
/*
$time_end = microtime(true);
$time = $time_end - $time_start;

echo $time_start . "<br>";
echo $time_end . "<br>";
echo $time;
*/
?>