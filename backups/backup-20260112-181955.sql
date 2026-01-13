--
-- PostgreSQL database dump
--

\restrict FHOqdXM9znEhixFPlTAMbt7qGJdrFTN0OesCzyJcVQJ9hQE21NYxuUl8hIAQBEe

-- Dumped from database version 16.10
-- Dumped by pg_dump version 16.10

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: customers; Type: TABLE; Schema: public; Owner: wk_user
--

CREATE TABLE public.customers (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    phone character varying(255),
    status character varying(255) DEFAULT 'active'::character varying NOT NULL,
    company character varying(255),
    address text,
    city character varying(255),
    state character varying(255),
    zip_code character varying(255),
    country character varying(255) DEFAULT 'Brasil'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.customers OWNER TO wk_user;

--
-- Name: leads; Type: TABLE; Schema: public; Owner: wk_user
--

CREATE TABLE public.leads (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255),
    phone character varying(255),
    status character varying(255) DEFAULT 'new'::character varying NOT NULL,
    source character varying(255),
    company character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    seller_id uuid
);


ALTER TABLE public.leads OWNER TO wk_user;

--
-- Name: migrations; Type: TABLE; Schema: public; Owner: wk_user
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO wk_user;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: wk_user
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO wk_user;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wk_user
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: model_has_permissions; Type: TABLE; Schema: public; Owner: wk_user
--

CREATE TABLE public.model_has_permissions (
    permission_id uuid NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id uuid NOT NULL
);


ALTER TABLE public.model_has_permissions OWNER TO wk_user;

--
-- Name: model_has_roles; Type: TABLE; Schema: public; Owner: wk_user
--

CREATE TABLE public.model_has_roles (
    role_id uuid NOT NULL,
    model_type character varying(255) NOT NULL,
    model_id uuid NOT NULL
);


ALTER TABLE public.model_has_roles OWNER TO wk_user;

--
-- Name: notifications; Type: TABLE; Schema: public; Owner: wk_user
--

CREATE TABLE public.notifications (
    id uuid NOT NULL,
    user_id uuid NOT NULL,
    type character varying(255) NOT NULL,
    title character varying(255) NOT NULL,
    message text NOT NULL,
    data json,
    action_url character varying(255),
    read_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.notifications OWNER TO wk_user;

--
-- Name: opportunities; Type: TABLE; Schema: public; Owner: wk_user
--

CREATE TABLE public.opportunities (
    id uuid NOT NULL,
    title character varying(255) NOT NULL,
    description text,
    value numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    status character varying(255) DEFAULT 'open'::character varying NOT NULL,
    customer_id uuid,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    client_id uuid,
    seller_id uuid,
    currency character varying(10) DEFAULT 'BRL'::character varying NOT NULL,
    probability integer,
    close_date date
);


ALTER TABLE public.opportunities OWNER TO wk_user;

--
-- Name: permissions; Type: TABLE; Schema: public; Owner: wk_user
--

CREATE TABLE public.permissions (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permissions OWNER TO wk_user;

--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: wk_user
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id uuid NOT NULL,
    name text NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.personal_access_tokens OWNER TO wk_user;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: wk_user
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personal_access_tokens_id_seq OWNER TO wk_user;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: wk_user
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: role_has_permissions; Type: TABLE; Schema: public; Owner: wk_user
--

CREATE TABLE public.role_has_permissions (
    permission_id uuid NOT NULL,
    role_id uuid NOT NULL
);


ALTER TABLE public.role_has_permissions OWNER TO wk_user;

--
-- Name: roles; Type: TABLE; Schema: public; Owner: wk_user
--

CREATE TABLE public.roles (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    guard_name character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.roles OWNER TO wk_user;

--
-- Name: sellers; Type: TABLE; Schema: public; Owner: wk_user
--

CREATE TABLE public.sellers (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255),
    phone character varying(255),
    role character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.sellers OWNER TO wk_user;

--
-- Name: users; Type: TABLE; Schema: public; Owner: wk_user
--

CREATE TABLE public.users (
    id uuid NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO wk_user;

--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Data for Name: customers; Type: TABLE DATA; Schema: public; Owner: wk_user
--

COPY public.customers (id, name, email, phone, status, company, address, city, state, zip_code, country, created_at, updated_at) FROM stdin;
88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3	Admin WK	admin@consultoriawk.com	\N	active	\N	\N	\N	\N	\N	Brasil	2026-01-11 11:56:28	2026-01-11 11:56:28
a4dcf156-023c-4b38-aa39-dd0e0b60d4a4	Admin WK	admin-test@wkcrm.local	000000000	active	\N	\N	\N	\N	\N	Brasil	2026-01-12 11:17:37	2026-01-12 11:17:37
f747753e-3210-474e-8fdb-eb3755a29a3d	Customer Test	customer-test@wkcrm.local	000000000	active	\N	\N	\N	\N	\N	Brasil	2026-01-12 17:51:24	2026-01-12 17:51:24
\.


--
-- Data for Name: leads; Type: TABLE DATA; Schema: public; Owner: wk_user
--

COPY public.leads (id, name, email, phone, status, source, company, created_at, updated_at, seller_id) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: wk_user
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	2025_10_16_094307_create_customers_table	1
2	2025_10_21_100000_create_leads_table	1
3	2025_10_21_100001_create_opportunities_table	1
4	2025_12_02_000000_create_leads_table	1
5	2025_12_02_000100_create_sellers_table	1
6	2025_12_03_000000_create_opportunities_table	1
7	2025_12_03_120000_add_seller_id_to_leads_table	1
8	2025_12_05_010000_add_value_to_opportunities_table	1
9	2025_12_07_000000_create_users_table	1
10	2025_12_08_105315_create_personal_access_tokens_table	1
11	2025_12_08_110659_create_permission_tables	1
12	2025_12_11_000000_create_notifications_table	1
13	2025_12_12_000000_make_opportunities_customer_id_nullable	1
\.


--
-- Data for Name: model_has_permissions; Type: TABLE DATA; Schema: public; Owner: wk_user
--

COPY public.model_has_permissions (permission_id, model_type, model_id) FROM stdin;
\.


--
-- Data for Name: model_has_roles; Type: TABLE DATA; Schema: public; Owner: wk_user
--

COPY public.model_has_roles (role_id, model_type, model_id) FROM stdin;
c0e3915f-4821-44fc-a5c4-21e0b2addb14	App\\Models\\User	88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3
c0e3915f-4821-44fc-a5c4-21e0b2addb14	App\\Models\\User	a4dcf156-023c-4b38-aa39-dd0e0b60d4a4
e1663d03-1455-4bf1-af98-4f88df48335d	App\\Models\\User	f747753e-3210-474e-8fdb-eb3755a29a3d
\.


--
-- Data for Name: notifications; Type: TABLE DATA; Schema: public; Owner: wk_user
--

COPY public.notifications (id, user_id, type, title, message, data, action_url, read_at, created_at, updated_at) FROM stdin;
a0d1e848-3ccc-400d-82c3-1aaba960b19e	88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3	opportunity_created	🎯 Nova Oportunidade	Oportunidade "Implantação CRM - Fase 2" foi criada. Valor: R$ 48.000,00	{"opportunity_id":"9d7c85fb-4108-471e-9ff7-948dea4741c1","opportunity_title":"Implanta\\u00e7\\u00e3o CRM - Fase 2","opportunity_value":48000,"seller_name":"Vendedor wk","created_by":"Admin WK"}	/opportunities/9d7c85fb-4108-471e-9ff7-948dea4741c1	\N	2026-01-12 13:07:36	2026-01-12 13:07:36
a0d1f20c-ef59-4adb-8c5e-ed37b95e2c30	88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3	opportunity_created	🎯 Nova Oportunidade	Oportunidade "Implantação CRM - Fase 1" foi criada. Valor: R$ 45.000,00	{"opportunity_id":"c61165bc-529b-40d9-aa71-c1172ec5711c","opportunity_title":"Implanta\\u00e7\\u00e3o CRM - Fase 1","opportunity_value":45000,"seller_name":"Vendedor wk","created_by":"Admin WK"}	/opportunities/c61165bc-529b-40d9-aa71-c1172ec5711c	\N	2026-01-12 13:34:55	2026-01-12 13:34:55
a0d255ff-850e-4926-87c2-c19c6151d1ee	88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3	opportunity_created	🎯 Nova Oportunidade	Oportunidade "Treinamento equipe de Vendas" foi criada. Valor: R$ 37.000,00	{"opportunity_id":"191573a6-2648-4981-9c0a-129360ec9e18","opportunity_title":"Treinamento equipe de Vendas","opportunity_value":37000,"seller_name":"N\\u00e3o atribu\\u00eddo","created_by":"Admin WK"}	/opportunities/191573a6-2648-4981-9c0a-129360ec9e18	\N	2026-01-12 18:14:23	2026-01-12 18:14:23
\.


--
-- Data for Name: opportunities; Type: TABLE DATA; Schema: public; Owner: wk_user
--

COPY public.opportunities (id, title, description, value, status, customer_id, created_at, updated_at, client_id, seller_id, currency, probability, close_date) FROM stdin;
0a1d8fe9-b537-40a1-9d4c-4380d04470fd	Implantação CRM - Fase 2	\N	49000.00	Em Negociação	88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3	2026-01-11 11:56:29	2026-01-11 11:56:29	88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3	52b1d81d-8dfe-4bb5-ab2b-95f1a59a4bce	BRL	99	\N
939da63c-70b7-4846-b136-f3c524f0eb88	Implantação CRM - Fase 1	\N	43000.00	Aberta	88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3	2026-01-11 12:24:06	2026-01-11 12:24:06	88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3	52b1d81d-8dfe-4bb5-ab2b-95f1a59a4bce	BRL	99	\N
722a84bc-0e16-43ba-86ab-3f5c25d33168	Treinar equipe de vendas	\N	21000.00	Proposta Enviada	88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3	2026-01-11 12:51:53	2026-01-11 12:51:53	88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3	52b1d81d-8dfe-4bb5-ab2b-95f1a59a4bce	BRL	90	\N
be939aaa-8498-4091-8b1a-16a063aabbe8	Treinamento de uso do sistema	\N	18000.00	Em Negociação	88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3	2026-01-11 13:03:06	2026-01-11 13:03:06	88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3	52b1d81d-8dfe-4bb5-ab2b-95f1a59a4bce	BRL	90	\N
9c2784a9-544c-41e8-827a-239e1f2f2d16	Implantação CRM - Fase 3	\N	51000.00	Em Negociação	88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3	2026-01-11 13:22:08	2026-01-11 13:22:08	88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3	52b1d81d-8dfe-4bb5-ab2b-95f1a59a4bce	BRL	99	\N
8af4cfda-1da1-4792-ad8e-4fd41a708a44	Instalação do Sistema CRM	\N	8000.00	Proposta Enviada	88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3	2026-01-12 11:59:27	2026-01-12 11:59:27	88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3	52b1d81d-8dfe-4bb5-ab2b-95f1a59a4bce	BRL	100	\N
9d7c85fb-4108-471e-9ff7-948dea4741c1	Implantação CRM - Fase 2	\N	48000.00	Em Negociação	a4dcf156-023c-4b38-aa39-dd0e0b60d4a4	2026-01-12 13:07:34	2026-01-12 13:07:34	a4dcf156-023c-4b38-aa39-dd0e0b60d4a4	52b1d81d-8dfe-4bb5-ab2b-95f1a59a4bce	BRL	99	\N
c61165bc-529b-40d9-aa71-c1172ec5711c	Implantação CRM - Fase 1	\N	45000.00	Em Negociação	a4dcf156-023c-4b38-aa39-dd0e0b60d4a4	2026-01-12 13:34:55	2026-01-12 13:34:55	a4dcf156-023c-4b38-aa39-dd0e0b60d4a4	52b1d81d-8dfe-4bb5-ab2b-95f1a59a4bce	BRL	100	\N
a39632f3-d114-4133-b66c-645496a344ef	Implantação CRM - Fase 1	\N	45000.00	open	f747753e-3210-474e-8fdb-eb3755a29a3d	2026-01-12 17:51:24	2026-01-12 17:51:24	\N	\N	BRL	40	\N
d90f2f8d-62e0-4a45-ae48-34718c50852b	Treinamento Times Comerciais	\N	18000.00	proposal	f747753e-3210-474e-8fdb-eb3755a29a3d	2026-01-12 17:51:24	2026-01-12 17:51:24	\N	\N	BRL	55	\N
2eb266f5-167b-46a6-8d82-9f6e0cc7d23f	Consultoria de Processos	\N	8000.00	open	f747753e-3210-474e-8fdb-eb3755a29a3d	2026-01-12 17:51:24	2026-01-12 17:51:24	\N	\N	BRL	80	\N
08e07f56-9918-48f4-aff4-b47231f53311	Sistema de Automação	\N	8000.00	won	f747753e-3210-474e-8fdb-eb3755a29a3d	2026-01-12 17:51:24	2026-01-12 17:51:24	\N	\N	BRL	100	\N
191573a6-2648-4981-9c0a-129360ec9e18	Treinamento equipe de Vendas	\N	37000.00	Em Negociação	a4dcf156-023c-4b38-aa39-dd0e0b60d4a4	2026-01-12 18:14:23	2026-01-12 18:14:23	a4dcf156-023c-4b38-aa39-dd0e0b60d4a4	\N	BRL	100	\N
\.


--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: wk_user
--

COPY public.permissions (id, name, guard_name, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: wk_user
--

COPY public.personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at) FROM stdin;
6	App\\Models\\User	a4dcf156-023c-4b38-aa39-dd0e0b60d4a4	test-token	2dcd1bffaa3be5d96869951c4a51913c217adefdd266c4427b199903a6b784ee	["*"]	2026-01-12 11:39:29	\N	2026-01-12 11:38:57	2026-01-12 11:39:29
9	App\\Models\\User	a4dcf156-023c-4b38-aa39-dd0e0b60d4a4	test-token	2dc028d22b5168653c389b2b4508a50b78aeb6c435cf782b659ee7f5f35a8ac2	["*"]	2026-01-12 18:14:43	\N	2026-01-12 17:05:21	2026-01-12 18:14:43
5	App\\Models\\User	a4dcf156-023c-4b38-aa39-dd0e0b60d4a4	test-token	c5d6e228beefca444aedd6f089c42790e8b742e64908d038727e1d29d0d042ea	["*"]	2026-01-12 11:25:44	\N	2026-01-12 11:17:37	2026-01-12 11:25:44
10	App\\Models\\User	a4dcf156-023c-4b38-aa39-dd0e0b60d4a4	test-token	6233c8990c3b53da599fc61a7596bea22c069b68d3a81a280109c2eec0d20dc0	["*"]	2026-01-12 17:46:30	\N	2026-01-12 17:46:30	2026-01-12 17:46:30
7	App\\Models\\User	a4dcf156-023c-4b38-aa39-dd0e0b60d4a4	test-token	0613525e53a2d9bde2b40cdc30b4ba9afa867ff9cc1a5e3a3ff186756b6fa45f	["*"]	2026-01-12 12:52:52	\N	2026-01-12 12:21:44	2026-01-12 12:52:52
11	App\\Models\\User	f747753e-3210-474e-8fdb-eb3755a29a3d	test-token	f6dec1868435cc89395f985e1cf494b64e6fb1c406736cbf997aa4dc020a0bdc	["*"]	2026-01-12 18:03:14	\N	2026-01-12 17:51:24	2026-01-12 18:03:15
\.


--
-- Data for Name: role_has_permissions; Type: TABLE DATA; Schema: public; Owner: wk_user
--

COPY public.role_has_permissions (permission_id, role_id) FROM stdin;
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: wk_user
--

COPY public.roles (id, name, guard_name, created_at, updated_at) FROM stdin;
c0e3915f-4821-44fc-a5c4-21e0b2addb14	admin	web	2026-01-11 14:29:14	2026-01-11 14:29:14
e1663d03-1455-4bf1-af98-4f88df48335d	customer	web	2026-01-11 14:29:14	2026-01-11 14:29:14
e3efee12-2c21-4e19-92c3-56866c6661d7	seller	web	2026-01-11 14:29:14	2026-01-11 14:29:14
\.


--
-- Data for Name: sellers; Type: TABLE DATA; Schema: public; Owner: wk_user
--

COPY public.sellers (id, name, email, phone, role, created_at, updated_at) FROM stdin;
52b1d81d-8dfe-4bb5-ab2b-95f1a59a4bce	Vendedor wk	vendedor@consultoriawk.com	(11) 99999-9999	\N	2026-01-11 14:32:44	2026-01-11 14:32:44
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: wk_user
--

COPY public.users (id, name, email, password, remember_token, created_at, updated_at) FROM stdin;
84e04541-656e-4358-bc47-a4e845b0d59c	Admin wk	admin@consultoriawk.com	$2y$12$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	\N	2026-01-11 14:27:00	2026-01-11 14:27:00
da73bbb8-9feb-4e16-9a3c-75455e32bc3c	Customer	customer@consultoriawk.com	$2y$12$WSgoFbTMtQUuQ1YB3jt.a.XMh4kO8jZeOB4cOlZXh88Avdd.kv5bW	\N	2026-01-11 10:42:30	2026-01-11 10:42:30
88cb0b5b-a55b-4f6f-98ae-bd7ee2b881d3	usuario	cristian@consultoriawk.com	$2y$12$VaNMzJs8TUsAXCYatxF9r.qUsX.OBgXJsdw/.MLGXUxqtDPS2rqs.	\N	2026-01-11 11:15:28	2026-01-11 11:15:28
a4dcf156-023c-4b38-aa39-dd0e0b60d4a4	Admin WK	admin-test@wkcrm.local	$2y$12$ApSggNm2Pqm2GtquKWAstuUOX/oeJeleyxNfQiAVeGjhxXYH2cjvK	\N	2026-01-12 11:17:37	2026-01-12 11:17:37
f747753e-3210-474e-8fdb-eb3755a29a3d	Customer Test	customer-test@wkcrm.local	$2y$12$.rYWK9UmZAoVWx1RJ4wG1uZDK4N3.7PivYQaxqti2V2NjyU.1.JIu	\N	2026-01-12 17:51:24	2026-01-12 17:51:24
\.


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: wk_user
--

SELECT pg_catalog.setval('public.migrations_id_seq', 13, true);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: wk_user
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 11, true);


--
-- Name: customers customers_email_unique; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_email_unique UNIQUE (email);


--
-- Name: customers customers_pkey; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_pkey PRIMARY KEY (id);


--
-- Name: leads leads_pkey; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.leads
    ADD CONSTRAINT leads_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: model_has_permissions model_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_pkey PRIMARY KEY (permission_id, model_id, model_type);


--
-- Name: model_has_roles model_has_roles_pkey; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_pkey PRIMARY KEY (role_id, model_id, model_type);


--
-- Name: notifications notifications_pkey; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_pkey PRIMARY KEY (id);


--
-- Name: opportunities opportunities_pkey; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.opportunities
    ADD CONSTRAINT opportunities_pkey PRIMARY KEY (id);


--
-- Name: permissions permissions_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: role_has_permissions role_has_permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_pkey PRIMARY KEY (permission_id, role_id);


--
-- Name: roles roles_name_guard_name_unique; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_guard_name_unique UNIQUE (name, guard_name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: sellers sellers_email_unique; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.sellers
    ADD CONSTRAINT sellers_email_unique UNIQUE (email);


--
-- Name: sellers sellers_pkey; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.sellers
    ADD CONSTRAINT sellers_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: customers_created_at_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX customers_created_at_index ON public.customers USING btree (created_at);


--
-- Name: customers_email_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX customers_email_index ON public.customers USING btree (email);


--
-- Name: customers_status_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX customers_status_index ON public.customers USING btree (status);


--
-- Name: leads_created_at_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX leads_created_at_index ON public.leads USING btree (created_at);


--
-- Name: leads_email_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX leads_email_index ON public.leads USING btree (email);


--
-- Name: leads_seller_id_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX leads_seller_id_index ON public.leads USING btree (seller_id);


--
-- Name: leads_status_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX leads_status_index ON public.leads USING btree (status);


--
-- Name: model_has_permissions_model_id_model_type_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX model_has_permissions_model_id_model_type_index ON public.model_has_permissions USING btree (model_id, model_type);


--
-- Name: model_has_roles_model_id_model_type_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX model_has_roles_model_id_model_type_index ON public.model_has_roles USING btree (model_id, model_type);


--
-- Name: notifications_user_id_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX notifications_user_id_index ON public.notifications USING btree (user_id);


--
-- Name: notifications_user_id_read_at_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX notifications_user_id_read_at_index ON public.notifications USING btree (user_id, read_at);


--
-- Name: opportunities_client_id_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX opportunities_client_id_index ON public.opportunities USING btree (client_id);


--
-- Name: opportunities_created_at_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX opportunities_created_at_index ON public.opportunities USING btree (created_at);


--
-- Name: opportunities_customer_id_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX opportunities_customer_id_index ON public.opportunities USING btree (customer_id);


--
-- Name: opportunities_seller_id_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX opportunities_seller_id_index ON public.opportunities USING btree (seller_id);


--
-- Name: opportunities_status_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX opportunities_status_index ON public.opportunities USING btree (status);


--
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: wk_user
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: model_has_permissions model_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.model_has_permissions
    ADD CONSTRAINT model_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: model_has_roles model_has_roles_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.model_has_roles
    ADD CONSTRAINT model_has_roles_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: notifications notifications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.notifications
    ADD CONSTRAINT notifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: opportunities opportunities_customer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.opportunities
    ADD CONSTRAINT opportunities_customer_id_foreign FOREIGN KEY (customer_id) REFERENCES public.customers(id) ON DELETE SET NULL;


--
-- Name: role_has_permissions role_has_permissions_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: role_has_permissions role_has_permissions_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: wk_user
--

ALTER TABLE ONLY public.role_has_permissions
    ADD CONSTRAINT role_has_permissions_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict FHOqdXM9znEhixFPlTAMbt7qGJdrFTN0OesCzyJcVQJ9hQE21NYxuUl8hIAQBEe

