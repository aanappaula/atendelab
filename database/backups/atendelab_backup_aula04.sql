-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 25/06/2026 às 00:50
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `atendelab`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `atendimentos`
--

CREATE TABLE `atendimentos` (
  `id` int(11) NOT NULL,
  `pessoa_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo_atendimento_id` int(11) NOT NULL,
  `data_atendimento` datetime DEFAULT current_timestamp(),
  `observacoes` text DEFAULT NULL,
  `status` enum('aberto','em_andamento','finalizado','cancelado') DEFAULT 'aberto'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `atendimentos`
--

INSERT INTO `atendimentos` (`id`, `pessoa_id`, `usuario_id`, `tipo_atendimento_id`, `data_atendimento`, `observacoes`, `status`) VALUES
(1, 1, 2, 1, '2026-06-10 09:15:00', 'Cliente solicitou informações sobre os serviços disponíveis.', 'finalizado'),
(2, 2, 2, 2, '2026-06-10 10:30:00', 'Problema relatado no acesso ao sistema.', 'em_andamento'),
(3, 3, 3, 3, '2026-06-11 14:00:00', 'Cliente registrou reclamação sobre atraso no atendimento.', 'finalizado'),
(4, 1, 3, 4, '2026-06-12 08:45:00', 'Atualização do telefone e endereço do cliente.', 'finalizado'),
(5, 4, 2, 5, '2026-06-12 16:20:00', 'Solicitação de visita técnica.', 'aberto');

-- --------------------------------------------------------

--
-- Estrutura para tabela `pessoas`
--

CREATE TABLE `pessoas` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `cpf` varchar(14) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `endereco` varchar(255) DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `pessoas`
--

INSERT INTO `pessoas` (`id`, `nome`, `cpf`, `telefone`, `email`, `endereco`, `criado_em`) VALUES
(1, 'João da Silva', '111.111.111-11', '(47) 99999-1111', 'joao@email.com', 'Rua das Flores, 123', '2026-06-13 13:47:15'),
(2, 'Maria Oliveira', '222.222.222-22', '(47) 99999-2222', 'maria@email.com', 'Av. Central, 456', '2026-06-13 13:47:15'),
(3, 'Pedro Santos', '333.333.333-33', '(47) 99999-3333', 'pedro@email.com', 'Rua do Comércio, 789', '2026-06-13 13:47:15'),
(4, 'Juliana Costa', '444.444.444-44', '(47) 99999-4444', 'juliana@email.com', 'Rua das Palmeiras, 321', '2026-06-13 13:47:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tipos_atendimentos`
--

CREATE TABLE `tipos_atendimentos` (
  `id` int(11) NOT NULL,
  `descricao` varchar(100) NOT NULL,
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tipos_atendimentos`
--

INSERT INTO `tipos_atendimentos` (`id`, `descricao`, `status`, `criado_em`) VALUES
(1, 'Informações', 'ativo', '2026-06-13 13:47:15'),
(2, 'Suporte Técnico', 'ativo', '2026-06-13 13:47:15'),
(3, 'Reclamação', 'ativo', '2026-06-13 13:47:15'),
(4, 'Atualização Cadastral', 'ativo', '2026-06-13 13:47:15'),
(5, 'Solicitação de Serviço', 'ativo', '2026-06-13 13:47:15');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('admin','aluno','atendente') DEFAULT 'atendente',
  `status` enum('ativo','inativo') DEFAULT 'ativo',
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `senha`, `perfil`, `status`, `criado_em`) VALUES
(2, 'Ana Paula de Souza', 'anapauladesouza@gmail.com', '$2y$10$OK4ZCTokwvXEeRUOQ2ly1ehcW.ulb9fvLe5QemFwZqwk8teiowYxy', 'atendente', 'ativo', '2026-06-09 23:09:30'),
(3, 'Inácio Junior', 'iiinacio@gmail.com', '$2y$10$uGh9QKUUjPd0gbDHjCl/g.7EtiHNYYSWHwBHvp3Qa9fZyzi2A1OHq', 'aluno', 'ativo', '2026-06-09 23:10:00'),
(4, 'Admin', 'admin@gmail.com', '$2y$10$pMmTiJyzhKcJzTknQYEJI.5WTaAXY20weYk0Fl6fWLrD/NU2WOFUe', 'admin', 'ativo', '2026-06-09 23:10:33'),
(5, 'Ana Souza', 'ana.souza@empresa.com', '123456', 'admin', 'ativo', '2026-06-13 13:47:15'),
(6, 'Carlos Pereira', 'carlos.pereira@empresa.com', '123456', 'atendente', 'ativo', '2026-06-13 13:47:15'),
(7, 'Mariana Lima', 'mariana.lima@empresa.com', '123456', 'atendente', 'ativo', '2026-06-13 13:47:15'),
(9, 'Administrador', 'admin@atendelab.com', '$2y$10$DLYKLgR8Enjdp1twus0fb.ZeUBkcb9Sn2OUag2dFftFtisCGd3QGi', 'admin', 'ativo', '2026-06-16 22:31:50');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `atendimentos`
--
ALTER TABLE `atendimentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_atendimento_pessoa` (`pessoa_id`),
  ADD KEY `fk_atendimento_usuario` (`usuario_id`),
  ADD KEY `fk_atendimento_tipo` (`tipo_atendimento_id`);

--
-- Índices de tabela `pessoas`
--
ALTER TABLE `pessoas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cpf` (`cpf`);

--
-- Índices de tabela `tipos_atendimentos`
--
ALTER TABLE `tipos_atendimentos`
  ADD PRIMARY KEY (`id`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `atendimentos`
--
ALTER TABLE `atendimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `pessoas`
--
ALTER TABLE `pessoas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `tipos_atendimentos`
--
ALTER TABLE `tipos_atendimentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `atendimentos`
--
ALTER TABLE `atendimentos`
  ADD CONSTRAINT `fk_atendimento_pessoa` FOREIGN KEY (`pessoa_id`) REFERENCES `pessoas` (`id`),
  ADD CONSTRAINT `fk_atendimento_tipo` FOREIGN KEY (`tipo_atendimento_id`) REFERENCES `tipos_atendimentos` (`id`),
  ADD CONSTRAINT `fk_atendimento_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
