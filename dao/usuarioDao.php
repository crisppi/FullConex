<?php

require_once("models/usuario.php");
require_once("models/message.php");

class UserDAO implements UserDAOInterface
{

    private $objfc;
    private $idFuncionario;
    private $nome;
    private $email_user;
    private $senha_user;

    private $conn;
    private $url;
    private $message;

    public function __construct(PDO $conn, $url)
    {
        $this->conn = $conn;
        $this->url = $url;
        $this->message = new Message($url);
    }

    public function buildUser($data)
    {

        $user = new Usuario();

        $user->id_usuario = $data["id_usuario"];
        $user->usuario_user = $data["usuario_user"];
        $user->login_user = $data["login_user"];
        $user->cpf_user = $data["cpf_user"];
        $user->sexo_user = $data["sexo_user"];
        $user->idade_user = $data["idade_user"];

        $user->email_user = $data["email_user"];
        $user->email02_user = $data["email02_user"];

        $user->senha_user = $data["senha_user"];
        $user->senha_default_user = $data["senha_default_user"];

        $user->endereco_user = $data["endereco_user"];
        $user->numero_user = $data["numero_user"];
        $user->bairro_user = $data["bairro_user"];
        $user->cidade_user = $data["cidade_user"];
        $user->estado_user = $data["estado_user"];

        $user->telefone01_user = $data["telefone01_user"];
        $user->telefone02_user = $data["telefone02_user"];

        $user->data_create_user = $data["data_create_user"];
        $user->usuario_create_user = $data["usuario_create_user"];
        $user->fk_usuario_user = $data["fk_usuario_user"];

        $user->vinculo_user = $data["vinculo_user"];
        $user->ativo_user = $data["ativo_user"];
        $user->data_admissao_user = $data["data_admissao_user"];
        $user->data_demissao_user = $data["data_demissao_user"];

        $user->nivel_user = $data["nivel_user"];
        $user->cargo_user = $data["cargo_user"];
        $user->depto_user = $data["depto_user"];
        $user->reg_profissional_user = $data["reg_profissional_user"];
        $user->tipo_reg_user = $data["tipo_reg_user"];
        $user->obs_user = $data["obs_user"];

        $user->foto_usuario = $data["foto_usuario"];

        return $user;
    }

    public function create(Usuario $usuario)
    {

        $stmt = $this->conn->prepare("INSERT INTO tb_user(
          usuario_user, 
          login_user, 
          sexo_user, 
          idade_user, 
          email_user, 
          email02_user, 
          senha_user, 
          senha_default_user, 
          endereco_user, 
          numero_user, 
          cidade_user, 
          bairro_user, 
          estado_user, 
          telefone01_user, 
          telefone02_user, 
          data_create_user, 
          usuario_create_user, 
          fk_usuario_user, 
          ativo_user, 
          data_admissao_user, 
          vinculo_user, 
          nivel_user, 
          cargo_user, 
          depto_user, 
          cpf_user, 
          obs_user, 
          tipo_reg_user,
          reg_profissional_user,
          foto_usuario
        ) VALUES (
          :usuario_user, 
          :login_user, 
          :sexo_user, 
          :idade_user, 
          :email_user, 
          :email02_user, 
          :senha_user, 
          :senha_default_user, 
          :endereco_user, 
          :numero_user, 
          :cidade_user, 
          :bairro_user, 
          :estado_user, 
          :telefone01_user, 
          :telefone02_user, 
          :data_create_user, 
          :usuario_create_user, 
          :fk_usuario_user, 
          :ativo_user, 
          :data_admissao_user, 
          :vinculo_user, 
          :nivel_user, 
          :cargo_user, 
          :depto_user, 
          :cpf_user, 
          :obs_user,
          :tipo_reg_user,
          :reg_profissional_user,
          :foto_usuario
        )");

        $stmt->bindParam(":usuario_user", $usuario->usuario_user);
        $stmt->bindParam(":login_user", $usuario->login_user);
        $stmt->bindParam(":sexo_user", $usuario->sexo_user);
        $stmt->bindParam(":idade_user", $usuario->idade_user);
        $stmt->bindParam(":endereco_user", $usuario->endereco_user);
        $stmt->bindParam(":email_user", $usuario->email_user);
        $stmt->bindParam(":senha_user", $usuario->senha_user);
        $stmt->bindParam(":senha_default_user", $usuario->senha_default_user);
        $stmt->bindParam(":email02_user", $usuario->email02_user);
        $stmt->bindParam(":telefone01_user", $usuario->telefone01_user);
        $stmt->bindParam(":telefone02_user", $usuario->telefone02_user);
        $stmt->bindParam(":bairro_user", $usuario->bairro_user);
        $stmt->bindParam(":numero_user", $usuario->numero_user);
        $stmt->bindParam(":cidade_user", $usuario->cidade_user);
        $stmt->bindParam(":estado_user", $usuario->estado_user);
        $stmt->bindParam(":ativo_user", $usuario->ativo_user);
        $stmt->bindParam(":data_admissao_user", $usuario->data_admissao_user);
        $stmt->bindParam(":data_create_user", $usuario->data_create_user);
        $stmt->bindParam(":usuario_create_user", $usuario->usuario_create_user);
        $stmt->bindParam(":fk_usuario_user", $usuario->fk_usuario_user);
        $stmt->bindParam(":vinculo_user", $usuario->vinculo_user);
        $stmt->bindParam(":nivel_user", $usuario->nivel_user);
        $stmt->bindParam(":cargo_user", $usuario->cargo_user);
        $stmt->bindParam(":depto_user", $usuario->depto_user);
        $stmt->bindParam(":cpf_user", $usuario->cpf_user);
        $stmt->bindParam(":obs_user", $usuario->obs_user);
        $stmt->bindParam(":reg_profissional_user", $usuario->reg_profissional_user);
        $stmt->bindParam(":tipo_reg_user", $usuario->tipo_reg_user);
        $stmt->bindParam(":foto_usuario", $usuario->foto_usuario);

        $stmt->execute();
    }

    public function update(Usuario $usuario)
    {

        $stmt = $this->conn->prepare("UPDATE tb_user SET
        usuario_user = :usuario_user,
        login_user = :login_user,
        fk_usuario_user = :fk_usuario_user,
        sexo_user = :sexo_user,
        idade_user = :idade_user,
        email_user = :email_user,
        email02_user = :email02_user,
        telefone01_user = :telefone01_user,
        telefone02_user = :telefone02_user,
        endereco_user = :endereco_user,
        numero_user = :numero_user,
        bairro_user = :bairro_user,
        cidade_user = :cidade_user,
        estado_user = :estado_user,
        usuario_create_user = :usuario_create_user,
        data_create_user = :data_create_user,
        ativo_user = :ativo_user,
        data_admissao_user = :data_admissao_user,
        data_demissao_user = :data_demissao_user,
        cargo_user = :cargo_user,
        depto_user = :depto_user,
        vinculo_user = :vinculo_user,
        nivel_user = :nivel_user,
        cpf_user = :cpf_user,
        reg_profissional_user = :reg_profissional_user,
        tipo_reg_user = :tipo_reg_user,
        senha_user = :senha_user,
        senha_default_user = :senha_default_user,
        obs_user =:obs_user,
        foto_usuario =:foto_usuario

        WHERE id_usuario = :id_usuario
      ");

        $stmt->bindParam(":usuario_user", $usuario->usuario_user);
        $stmt->bindParam(":login_user", $usuario->login_user);
        $stmt->bindParam(":fk_usuario_user", $usuario->fk_usuario_user);
        $stmt->bindParam(":sexo_user", $usuario->sexo_user);
        $stmt->bindParam(":idade_user", $usuario->idade_user);

        $stmt->bindParam(":email_user", $usuario->email_user);
        $stmt->bindParam(":email02_user", $usuario->email02_user);

        $stmt->bindParam(":telefone01_user", $usuario->telefone01_user);
        $stmt->bindParam(":telefone02_user", $usuario->telefone02_user);

        $stmt->bindParam(":endereco_user", $usuario->endereco_user);
        $stmt->bindParam(":numero_user", $usuario->numero_user);
        $stmt->bindParam(":bairro_user", $usuario->bairro_user);
        $stmt->bindParam(":cidade_user", $usuario->cidade_user);
        $stmt->bindParam(":estado_user", $usuario->estado_user);

        $stmt->bindParam(":usuario_create_user", $usuario->usuario_create_user);
        $stmt->bindParam(":data_create_user", $usuario->data_create_user);

        $stmt->bindParam(":ativo_user", $usuario->ativo_user);
        $stmt->bindParam(":data_admissao_user", $usuario->data_admissao_user);
        $stmt->bindParam(":data_demissao_user", $usuario->data_demissao_user);

        $stmt->bindParam(":cargo_user", $usuario->cargo_user);
        $stmt->bindParam(":depto_user", $usuario->depto_user);
        $stmt->bindParam(":vinculo_user", $usuario->vinculo_user);
        $stmt->bindParam(":nivel_user", $usuario->nivel_user);

        $stmt->bindParam(":cpf_user", $usuario->cpf_user);
        $stmt->bindParam(":obs_user", $usuario->obs_user);

        $stmt->bindParam(":reg_profissional_user", $usuario->reg_profissional_user);
        $stmt->bindParam(":tipo_reg_user", $usuario->tipo_reg_user);

        $stmt->bindParam(":senha_user", $usuario->senha_user);
        $stmt->bindParam(":senha_default_user", $usuario->senha_default_user);

        $stmt->bindParam(":id_usuario", $usuario->id_usuario);
        $stmt->bindParam(":foto_usuario", $usuario->foto_usuario);

        $stmt->execute();

        if (5 > 3) {

            // Redireciona para o perfil do usuario
            $this->message->setMessage("Dados atualizados com sucesso!", "success", "list_usuario.php");
        }
    }


    public function findAll()
    {
        $usuario = [];

        $stmt = $this->conn->prepare("SELECT * FROM tb_user
        ORDER BY id_usuario DESC");

        $stmt->execute();

        $usuario = $stmt->fetchAll();
        return $usuario;
    }

    public function findAllMensagens($usuario_logado)
    {
        $usuario = [];

        $sql = "
            SELECT u.id_usuario,
                   u.usuario_user,
                   u.foto_usuario,
                   m.mensagem AS ultima_mensagem,
                   m.data_mensagem,
                   m.vista,
                   m.para_usuario
            FROM tb_user u
            LEFT JOIN (
                SELECT m1.*
                FROM tb_mensagem m1
                INNER JOIN (
                    SELECT 
                        CASE 
                            WHEN m.de_usuario = :usuario_logado_main THEN m.para_usuario 
                            ELSE m.de_usuario 
                        END AS outro_usuario,
                        MAX(m.id_mensagem) AS ultima_id_mensagem
                    FROM tb_mensagem m
                    WHERE m.de_usuario = :usuario_logado_sub1 OR m.para_usuario = :usuario_logado_sub2
                    GROUP BY outro_usuario
                ) AS ultimas_mensagens
                ON m1.id_mensagem = ultimas_mensagens.ultima_id_mensagem
            ) AS m ON (u.id_usuario = m.de_usuario OR u.id_usuario = m.para_usuario)
            WHERE u.id_usuario != :usuario_logado
              AND u.ativo_user = 's'
            ORDER BY m.data_mensagem DESC";

        $stmt = $this->conn->prepare($sql);

        $stmt->bindParam(':usuario_logado', $usuario_logado, PDO::PARAM_INT);
        $stmt->bindParam(':usuario_logado_main', $usuario_logado, PDO::PARAM_INT);
        $stmt->bindParam(':usuario_logado_sub1', $usuario_logado, PDO::PARAM_INT);
        $stmt->bindParam(':usuario_logado_sub2', $usuario_logado, PDO::PARAM_INT);
        $stmt->execute();

        // Busca os resultados
        $usuario = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $usuario;
    }

    public function findByLogin(Usuario $user)
    {

        if ($user != "") {

            $stmt = $this->conn->prepare("SELECT * FROM tb_user WHERE usuario_user = :username AND senha_user =:senha_login");

            $stmt->bindParam(":usuario_user", $user->usuario_user);
            $stmt->bindParam(":senha_user", $user->senha_user);

            $stmt->execute();


            $data = $stmt->fetch();
            $user = $this->buildUser($data);

            return $user;
        }
    }

    public function findByEmail($username)
    {

        if ($username != "") {

            $stmt = $this->conn->prepare("SELECT * FROM tb_user WHERE email_user = :username");

            $stmt->bindParam(":email_user", $username);

            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $data = $stmt->fetch();
                $user = $this->buildUser($data);

                return $user;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    public function findById_user($id_usuario)
    {

        if ($id_usuario != "") {

            $stmt = $this->conn->prepare("SELECT * FROM tb_user WHERE id_usuario = :id_usuario");

            $stmt->bindParam(":id_usuario", $id_usuario);

            $stmt->execute();

            if ($stmt->rowCount() > 0) {

                $data = $stmt->fetch();
                $usuario = $this->buildUser($data);

                return $usuario;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }


    public function destroy($id_usuario)
    {

        // Remove o token da session
        $stmt = $this->conn->prepare("DELETE FROM tb_user WHERE id_usuario = :id_usuario");

        $stmt->bindParam(":id_usuario", $id_usuario);

        $stmt->execute();

        // Redirecionar e apresentar a mensagem de sucesso
        $this->message->setMessage("Deletado!", "success", "/list_usuario.php");
    }

    public function changePassword(Usuario $user)
    {

        $stmt = $this->conn->prepare("UPDATE tb_user SET
        senha_user = :senha_user
        WHERE id_usuario = :id_usuario
      ");

        $stmt->bindParam(":senha_user", $user->senha_user);
        $stmt->bindParam(":id_usuario", $user->id_usuario);

        $stmt->execute();

        // Redirecionar e apresentar a mensagem de sucesso
        $this->message->setMessage("senha alterada com sucesso!", "success", "editprofile.php");
    }

    public function findGeral()
    {

        $usuarios = [];

        $stmt = $this->conn->query("SELECT * FROM tb_user ORDER BY id_usuario DESC");

        $stmt->execute();

        $usuarios = $stmt->fetchAll();

        return $usuarios;
    }

    public function findByUser($pesquisa_nome)
    {

        $usuario = [];

        $stmt = $this->conn->prepare("SELECT * FROM tb_user
                                    WHERE usuario_user LIKE :usuario_user ");

        $stmt->bindValue(":usuario_user", '%' . $pesquisa_nome . '%');

        $stmt->execute();

        $usuario = $stmt->fetchAll();
        return $usuario;
    }
    public function findGeralUsuario()
    {

        $usuarios = [];

        $stmt = $this->conn->query("SELECT * FROM tb_user ORDER BY id_usuario DESC");

        $stmt->execute();

        $usuarios = $stmt->fetchAll();

        return $usuarios;
    }

    public function sairFuncionario()
    {
        session_destroy();
        header('location: http://localhost/login');
    }

    # METODO DE SELECAO COM VARIAVEIS NO QUERY
    public function selectAllUsuario($where = null, $order = null, $limit = null)
    {
        //DADOS DA QUERY
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $order = strlen($order) ? 'ORDER BY ' . $order : '';
        $limit = strlen($limit) ? 'LIMIT ' . $limit : '';

        //MONTA A QUERY
        $query = $this->conn->query('SELECT * FROM tb_user ' . $where . ' ' . $order . ' ' . $limit);

        $query->execute();

        $usuario = $query->fetchAll();

        return $usuario;
    }
    public function QtdUsuario($where = null, $order = null, $limite = null)
    {
        $estipulante = [];
        //DADOS DA QUERY
        $where = strlen($where) ? 'WHERE ' . $where : '';
        $order = strlen($order) ? 'ORDER BY ' . $order : '';
        $limite = strlen($limite) ? 'LIMIT ' . $limite : '';

        $stmt = $this->conn->query('SELECT * ,COUNT(id_usuario) as qtd FROM tb_user ' . $where . ' ' . $order . ' ' . $limite);

        $stmt->execute();

        $QtdTotalUser = $stmt->fetch();

        return $QtdTotalUser;
    }
    public function findAtivosByCargos(array $cargos): array
    {
        if (empty($cargos)) return [];

        // normaliza para minúsculas
        $cargos = array_values(array_unique(array_map(
            fn($c) => mb_strtolower(trim((string)$c), 'UTF-8'),
            $cargos
        )));

        // monta placeholders
        $placeholders = implode(',', array_fill(0, count($cargos), '?'));

        // usa LOWER(cargo_user) para comparação case-insensitive
        $sql = "
            SELECT id_usuario, usuario_user, cargo_user, ativo_user
            FROM tb_user
            WHERE ativo_user IN ('s','S','1','true','TRUE','ativo','ATIVO')
              AND LOWER(cargo_user) IN ($placeholders)
            ORDER BY usuario_user ASC
        ";

        $stmt = $this->conn->prepare($sql);
        foreach ($cargos as $i => $cargo) {
            $stmt->bindValue($i + 1, $cargo);
        }
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Retorna médicos auditores e enfermeiros auditores ATIVOS.
     * Campos: id_usuario, usuario_user, cargo_user, ativo_user.
     */
    public function findMedicosEnfermeiros(): array
    {
        $cargos = ['med_auditor', 'medico_auditor', 'enf_auditor', 'enfer_auditor'];
        return $this->findAtivosByCargos($cargos);
    }

    public function findAdministrativos(): array
    {
        // cobre variações comuns de cadastro
        $cargos = ['administrativo', 'adm', 'administrador'];
        return $this->findAtivosByCargos($cargos);
    }
}
