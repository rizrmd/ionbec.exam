--
-- PostgreSQL database dump
--

\restrict wWmvEhqT2YAbTxBJr66BtlZKV1nx2XoG3ZReBqUy4AEi4IAb35CScik869vVJgn

-- Dumped from database version 16.9
-- Dumped by pg_dump version 17.6

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
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
-- Name: activity_log; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.activity_log (
    id integer NOT NULL,
    log_name character varying(255),
    description character varying(255) NOT NULL,
    subject_id integer,
    subject_type character varying(255),
    causer_id integer,
    causer_type character varying(255),
    properties text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.activity_log OWNER TO postgres;

--
-- Name: activity_log_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.activity_log_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.activity_log_id_seq OWNER TO postgres;

--
-- Name: activity_log_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.activity_log_id_seq OWNED BY public.activity_log.id;


--
-- Name: answers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.answers (
    id integer NOT NULL,
    question_id integer NOT NULL,
    answer text,
    is_correct_answer boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    hash character varying(255)
);


ALTER TABLE public.answers OWNER TO postgres;

--
-- Name: answers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.answers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.answers_id_seq OWNER TO postgres;

--
-- Name: answers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.answers_id_seq OWNED BY public.answers.id;


--
-- Name: attachables; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.attachables (
    attachment_id uuid NOT NULL,
    attachable_id integer NOT NULL,
    attachable_type character varying(255) NOT NULL
);


ALTER TABLE public.attachables OWNER TO postgres;

--
-- Name: attachments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.attachments (
    id uuid NOT NULL,
    type character varying(255) DEFAULT 'attachment'::character varying NOT NULL,
    uploaded_by integer NOT NULL,
    title character varying(255),
    path character varying(255),
    mime character varying(255),
    description text,
    options text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    client_id bigint
);


ALTER TABLE public.attachments OWNER TO postgres;

--
-- Name: attempt_question; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.attempt_question (
    id integer NOT NULL,
    attempt_id integer NOT NULL,
    question_id integer NOT NULL,
    answer_id integer,
    answer_hash character varying(255),
    answer text,
    is_correct boolean DEFAULT false NOT NULL,
    score numeric(8,2) DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.attempt_question OWNER TO postgres;

--
-- Name: attempt_question_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.attempt_question_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.attempt_question_id_seq OWNER TO postgres;

--
-- Name: attempt_question_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.attempt_question_id_seq OWNED BY public.attempt_question.id;


--
-- Name: attempts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.attempts (
    id integer NOT NULL,
    attempted_by integer NOT NULL,
    exam_id integer NOT NULL,
    delivery_id integer NOT NULL,
    ip_address character varying(255) NOT NULL,
    started_at timestamp(0) without time zone,
    ended_at timestamp(0) without time zone,
    extra_minute integer DEFAULT 0 NOT NULL,
    score numeric(8,2) DEFAULT 0 NOT NULL,
    progress integer DEFAULT 0 NOT NULL,
    penalty integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    finish_scoring boolean DEFAULT false NOT NULL,
    client_id bigint,
    finished_at timestamp(0) without time zone,
    hash character varying(255)
);


ALTER TABLE public.attempts OWNER TO postgres;

--
-- Name: attempts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.attempts_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.attempts_id_seq OWNER TO postgres;

--
-- Name: attempts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.attempts_id_seq OWNED BY public.attempts.id;


--
-- Name: categories; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.categories (
    id integer NOT NULL,
    type character varying(255) DEFAULT 'category'::character varying NOT NULL,
    code character varying(255),
    parent integer DEFAULT 0 NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    client_id bigint,
    hash character varying(255)
);


ALTER TABLE public.categories OWNER TO postgres;

--
-- Name: categories_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.categories_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.categories_id_seq OWNER TO postgres;

--
-- Name: categories_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.categories_id_seq OWNED BY public.categories.id;


--
-- Name: category_item; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.category_item (
    category_id integer NOT NULL,
    item_id integer NOT NULL
);


ALTER TABLE public.category_item OWNER TO postgres;

--
-- Name: category_question; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.category_question (
    category_id integer NOT NULL,
    question_id integer NOT NULL
);


ALTER TABLE public.category_question OWNER TO postgres;

--
-- Name: clients; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.clients (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    domains json NOT NULL,
    is_active boolean DEFAULT true NOT NULL,
    settings json,
    primary_contact_email character varying(255),
    primary_contact_phone character varying(255),
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    logo character varying(255)
);


ALTER TABLE public.clients OWNER TO postgres;

--
-- Name: clients_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.clients_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.clients_id_seq OWNER TO postgres;

--
-- Name: clients_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.clients_id_seq OWNED BY public.clients.id;


--
-- Name: deliveries; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.deliveries (
    id integer NOT NULL,
    exam_id integer NOT NULL,
    group_id integer NOT NULL,
    name character varying(255),
    scheduled_at timestamp(0) without time zone,
    duration integer DEFAULT 60 NOT NULL,
    ended_at timestamp(0) without time zone,
    is_anytime boolean DEFAULT false NOT NULL,
    automatic_start boolean DEFAULT true NOT NULL,
    is_finished timestamp(0) without time zone,
    last_status character varying(255),
    display_name character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    client_id bigint,
    hash character varying(255)
);


ALTER TABLE public.deliveries OWNER TO postgres;

--
-- Name: deliveries_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.deliveries_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.deliveries_id_seq OWNER TO postgres;

--
-- Name: deliveries_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.deliveries_id_seq OWNED BY public.deliveries.id;


--
-- Name: delivery_snapshots; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.delivery_snapshots (
    id bigint NOT NULL,
    delivery_id bigint NOT NULL,
    exam_id bigint NOT NULL,
    exam_structure jsonb NOT NULL,
    total_questions integer DEFAULT 0 NOT NULL,
    total_items integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.delivery_snapshots OWNER TO postgres;

--
-- Name: delivery_snapshots_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.delivery_snapshots_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.delivery_snapshots_id_seq OWNER TO postgres;

--
-- Name: delivery_snapshots_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.delivery_snapshots_id_seq OWNED BY public.delivery_snapshots.id;


--
-- Name: delivery_taker; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.delivery_taker (
    delivery_id integer NOT NULL,
    taker_id integer NOT NULL,
    token character varying(255),
    is_login boolean DEFAULT false NOT NULL
);


ALTER TABLE public.delivery_taker OWNER TO postgres;

--
-- Name: exam_item; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.exam_item (
    exam_id integer NOT NULL,
    item_id integer NOT NULL,
    "order" integer DEFAULT 0 NOT NULL
);


ALTER TABLE public.exam_item OWNER TO postgres;

--
-- Name: exam_session_logs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.exam_session_logs (
    id bigint NOT NULL,
    attempt_id bigint,
    session_key character varying(255) NOT NULL,
    tab_count integer DEFAULT 1 NOT NULL,
    ip_address character varying(45),
    country character varying(100),
    city character varying(100),
    isp character varying(255),
    user_agent text,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.exam_session_logs OWNER TO postgres;

--
-- Name: exam_session_logs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.exam_session_logs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.exam_session_logs_id_seq OWNER TO postgres;

--
-- Name: exam_session_logs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.exam_session_logs_id_seq OWNED BY public.exam_session_logs.id;


--
-- Name: exams; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.exams (
    id integer NOT NULL,
    code character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    options text,
    is_random boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    is_mcq boolean,
    is_interview boolean DEFAULT false NOT NULL,
    client_id bigint,
    is_published boolean DEFAULT false NOT NULL,
    title character varying(255),
    hash character varying(255)
);


ALTER TABLE public.exams OWNER TO postgres;

--
-- Name: exams_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.exams_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.exams_id_seq OWNER TO postgres;

--
-- Name: exams_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.exams_id_seq OWNED BY public.exams.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: group_taker; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.group_taker (
    taker_id integer NOT NULL,
    group_id integer NOT NULL,
    taker_code character varying(255)
);


ALTER TABLE public.group_taker OWNER TO postgres;

--
-- Name: groups; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.groups (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    description text,
    code character varying(255),
    last_taker_code integer DEFAULT 1 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    closed_at timestamp(0) without time zone,
    client_id bigint,
    hash character varying(255)
);


ALTER TABLE public.groups OWNER TO postgres;

--
-- Name: groups_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.groups_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.groups_id_seq OWNER TO postgres;

--
-- Name: groups_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.groups_id_seq OWNED BY public.groups.id;


--
-- Name: items; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.items (
    id integer NOT NULL,
    title character varying(255) NOT NULL,
    content text,
    type character varying(255) DEFAULT 'simple'::character varying NOT NULL,
    is_vignette boolean DEFAULT false NOT NULL,
    is_random boolean DEFAULT false NOT NULL,
    score integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    client_id bigint,
    hash character varying(255)
);


ALTER TABLE public.items OWNER TO postgres;

--
-- Name: items_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.items_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.items_id_seq OWNER TO postgres;

--
-- Name: items_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.items_id_seq OWNED BY public.items.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_resets; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_resets (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_resets OWNER TO postgres;

--
-- Name: permission_role; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.permission_role (
    id integer NOT NULL,
    permission_id integer NOT NULL,
    role_id integer NOT NULL,
    granted boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permission_role OWNER TO postgres;

--
-- Name: permission_role_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.permission_role_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permission_role_id_seq OWNER TO postgres;

--
-- Name: permission_role_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permission_role_id_seq OWNED BY public.permission_role.id;


--
-- Name: permission_user; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.permission_user (
    id integer NOT NULL,
    permission_id integer NOT NULL,
    user_id integer NOT NULL,
    granted boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permission_user OWNER TO postgres;

--
-- Name: permission_user_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.permission_user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permission_user_id_seq OWNER TO postgres;

--
-- Name: permission_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permission_user_id_seq OWNED BY public.permission_user.id;


--
-- Name: permissions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.permissions (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    description character varying(255),
    model character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.permissions OWNER TO postgres;

--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.permissions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.permissions_id_seq OWNER TO postgres;

--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id bigint NOT NULL,
    name character varying(255) NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.personal_access_tokens OWNER TO postgres;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.personal_access_tokens_id_seq OWNER TO postgres;

--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: questions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.questions (
    id integer NOT NULL,
    item_id integer NOT NULL,
    type character varying(255) DEFAULT 'simple'::character varying NOT NULL,
    question text,
    is_random boolean DEFAULT false NOT NULL,
    score integer DEFAULT 100 NOT NULL,
    "order" integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    client_id bigint,
    hash character varying(255)
);


ALTER TABLE public.questions OWNER TO postgres;

--
-- Name: questions_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.questions_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.questions_id_seq OWNER TO postgres;

--
-- Name: questions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.questions_id_seq OWNED BY public.questions.id;


--
-- Name: register_data; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.register_data (
    id bigint NOT NULL,
    taker_id bigint NOT NULL,
    taker_code character varying(255),
    delivery_id bigint NOT NULL,
    group_id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.register_data OWNER TO postgres;

--
-- Name: register_data_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.register_data_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.register_data_id_seq OWNER TO postgres;

--
-- Name: register_data_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.register_data_id_seq OWNED BY public.register_data.id;


--
-- Name: role_user; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.role_user (
    id integer NOT NULL,
    role_id integer NOT NULL,
    user_id integer NOT NULL,
    granted boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.role_user OWNER TO postgres;

--
-- Name: role_user_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.role_user_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.role_user_id_seq OWNER TO postgres;

--
-- Name: role_user_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.role_user_id_seq OWNED BY public.role_user.id;


--
-- Name: roles; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.roles (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    slug character varying(255) NOT NULL,
    description character varying(255),
    parent_id integer,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    is_system boolean DEFAULT false NOT NULL,
    permissions json,
    client_id bigint
);


ALTER TABLE public.roles OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.roles_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.roles_id_seq OWNER TO postgres;

--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: takers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.takers (
    id integer NOT NULL,
    name character varying(255) NOT NULL,
    reg character varying(255),
    email character varying(255),
    password character varying(255),
    is_verified boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    client_id bigint
);


ALTER TABLE public.takers OWNER TO postgres;

--
-- Name: takers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.takers_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.takers_id_seq OWNER TO postgres;

--
-- Name: takers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.takers_id_seq OWNED BY public.takers.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id integer NOT NULL,
    avatar character varying(255),
    name character varying(255) NOT NULL,
    username character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    gender character varying(255) DEFAULT 'other'::character varying NOT NULL,
    profile_photo_path character varying(2048),
    birthplace character varying(255),
    birthday date,
    remember_token character varying(100),
    last_login timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    two_factor_secret text,
    two_factor_recovery_codes text,
    client_id bigint,
    is_admin boolean DEFAULT false NOT NULL,
    admin_role character varying(50) DEFAULT 'viewer'::character varying NOT NULL,
    CONSTRAINT users_gender_check CHECK (((gender)::text = ANY ((ARRAY['male'::character varying, 'female'::character varying, 'other'::character varying])::text[])))
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: activity_log id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activity_log ALTER COLUMN id SET DEFAULT nextval('public.activity_log_id_seq'::regclass);


--
-- Name: answers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.answers ALTER COLUMN id SET DEFAULT nextval('public.answers_id_seq'::regclass);


--
-- Name: attempt_question id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attempt_question ALTER COLUMN id SET DEFAULT nextval('public.attempt_question_id_seq'::regclass);


--
-- Name: attempts id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attempts ALTER COLUMN id SET DEFAULT nextval('public.attempts_id_seq'::regclass);


--
-- Name: categories id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories ALTER COLUMN id SET DEFAULT nextval('public.categories_id_seq'::regclass);


--
-- Name: clients id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clients ALTER COLUMN id SET DEFAULT nextval('public.clients_id_seq'::regclass);


--
-- Name: deliveries id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.deliveries ALTER COLUMN id SET DEFAULT nextval('public.deliveries_id_seq'::regclass);


--
-- Name: delivery_snapshots id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.delivery_snapshots ALTER COLUMN id SET DEFAULT nextval('public.delivery_snapshots_id_seq'::regclass);


--
-- Name: exam_session_logs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.exam_session_logs ALTER COLUMN id SET DEFAULT nextval('public.exam_session_logs_id_seq'::regclass);


--
-- Name: exams id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.exams ALTER COLUMN id SET DEFAULT nextval('public.exams_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: groups id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groups ALTER COLUMN id SET DEFAULT nextval('public.groups_id_seq'::regclass);


--
-- Name: items id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.items ALTER COLUMN id SET DEFAULT nextval('public.items_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: permission_role id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permission_role ALTER COLUMN id SET DEFAULT nextval('public.permission_role_id_seq'::regclass);


--
-- Name: permission_user id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permission_user ALTER COLUMN id SET DEFAULT nextval('public.permission_user_id_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: questions id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.questions ALTER COLUMN id SET DEFAULT nextval('public.questions_id_seq'::regclass);


--
-- Name: register_data id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.register_data ALTER COLUMN id SET DEFAULT nextval('public.register_data_id_seq'::regclass);


--
-- Name: role_user id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_user ALTER COLUMN id SET DEFAULT nextval('public.role_user_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: takers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.takers ALTER COLUMN id SET DEFAULT nextval('public.takers_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: activity_log; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.activity_log (id, log_name, description, subject_id, subject_type, causer_id, causer_type, properties, created_at, updated_at) FROM stdin;
1	New Delivery Created!	Delivery TEST25 Created	151	App\\Models\\Deliveries\\Delivery	1	App\\Models\\Accounts\\User	\N	2025-11-04 14:03:03	2025-11-04 14:03:03
2	New Delivery Created!	Delivery Test2025 Created	152	App\\Models\\Deliveries\\Delivery	1	App\\Models\\Accounts\\User	\N	2025-11-04 17:41:44	2025-11-04 17:41:44
3	New Delivery Created!	Delivery osce test Created	153	App\\Models\\Deliveries\\Delivery	3	App\\Models\\Accounts\\User	\N	2025-11-04 21:31:10	2025-11-04 21:31:10
4	New Delivery Created!	Delivery mcq test Created	154	App\\Models\\Deliveries\\Delivery	3	App\\Models\\Accounts\\User	\N	2025-11-04 21:32:36	2025-11-04 21:32:36
\.


--
-- Data for Name: answers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.answers (id, question_id, answer, is_correct_answer, created_at, updated_at, hash) FROM stdin;
63	1066	<p style="text-align: justify">metacarpophalangeal (MP) joint flexion.</p>	f	2025-11-04 21:25:46	2025-11-04 21:25:46	wyr1bYMd
67	1069	<p style="text-align: justify">metacarpophalangeal (MP) joint extension.</p>	f	2025-11-04 21:27:59	2025-11-04 21:27:59	bGXwgOX5
68	1069	<p style="text-align: justify">proximal interphalangeal (PIP) joint extension.</p>	t	2025-11-04 21:27:59	2025-11-04 21:27:59	An7kJeXD
69	1069	<p style="text-align: justify">metacarpophalangeal (MP) joint flexion.</p>	f	2025-11-04 21:27:59	2025-11-04 21:27:59	yZX5K2rl
70	1069	<p style="text-align: justify">proximal interphalangeal (PIP) joint flexion.</p>	f	2025-11-04 21:27:59	2025-11-04 21:27:59	Wkr2j5r5
71	1070	<p style="text-align: justify">CT scan</p>	f	2025-11-04 21:34:05	2025-11-04 21:34:05	axrqawr2
72	1070	<p style="text-align: justify">Bone scan</p>	f	2025-11-04 21:34:05	2025-11-04 21:34:05	L8M9JxXv
73	1070	<p style="text-align: justify">MRI</p>	t	2025-11-04 21:34:05	2025-11-04 21:34:05	vDXQP9M0
74	1070	<p style="text-align: justify">Ultrasound</p>	f	2025-11-04 21:34:05	2025-11-04 21:34:05	5nMo8dXv
75	1070	<p style="text-align: justify">Repeat X-ray after 10 days</p>	f	2025-11-04 21:34:05	2025-11-04 21:34:05	YqXKNGML
76	1071	<p style="text-align: justify">Surgical technique</p>	f	2025-11-04 21:37:39	2025-11-04 21:37:39	GD7WN6Xd
78	1071	<p style="text-align: justify">Type of implant</p>	f	2025-11-04 21:37:39	2025-11-04 21:37:39	NgrzDEXk
79	1071	<p style="text-align: justify">Blood transfusion requirement</p>	f	2025-11-04 21:37:39	2025-11-04 21:37:39	OErEN2Mg
110	1078	<p style="text-align: justify">Repetitive valgus loading during gait</p>	f	2025-11-04 22:03:06	2025-11-04 22:03:06	\N
1	1	<p>Emergent surgery, including open carpal tunnel release, open reduction of the perilunate dislocation, repair of the scapholunate ligament, and intercarpal pinning</p>	t	2025-11-04 13:03:53	2025-11-04 13:03:53	gxXeLMAR
2	1	<p>Emergent surgery, including open carpal tunnel release, closed reduction of the perilunate dislocation, and casting</p>	f	2025-11-04 13:03:53	2025-11-04 13:03:53	B6MGa7zw
3	1	<p>Elective outpatient surgery, including open carpal tunnel release, open reduction of the perilunate dislocation, repair of the scapholunate ligament, and intercarpal pinning</p>	f	2025-11-04 13:03:53	2025-11-04 13:03:53	5bMNvMyp
4	1	<p>Emergent surgery, including open reduction of the perilunate dislocation, repair of the scapholunate ligament, and intercarpal pinning</p>	f	2025-11-04 13:03:53	2025-11-04 13:03:53	Parp1MK8
5	1	<p>Emergent surgery, including open reduction of the perilunate dislocation, repair of the scapholunate ligament, and intercarpal pinning</p>	f	2025-11-04 13:03:53	2025-11-04 13:03:53	Gn7b4MN3
11	3	<p>Osteosarcoma</p>	f	2025-11-04 13:06:37	2025-11-04 13:06:37	oP7l6M3d
12	3	<p>Enchondroma</p>	f	2025-11-04 13:06:37	2025-11-04 13:06:37	3L7gxrnY
13	3	<p>Fibrous dysplasia</p>	f	2025-11-04 13:06:37	2025-11-04 13:06:37	Qm7vmXNj
14	3	<p>Chondrosarcoma</p>	t	2025-11-04 13:06:37	2025-11-04 13:06:37	bRXdlXmN
15	3	<p>Chondromyxoid fibroma</p>	f	2025-11-04 13:06:37	2025-11-04 13:06:37	q6MjJMRy
16	4	<p>MRI and biopsy from an orthopedic oncologist</p>	f	2025-11-04 13:11:59	2025-11-04 13:11:59	RNrLqMp0
17	4	<p>Marginal resection with complete removal of cartilage cap</p>	t	2025-11-04 13:11:59	2025-11-04 13:11:59	k276krDp
18	4	<p>Educate the family about the benign nature of the condition and prescribe NSAIDs until the pain diminishes</p>	f	2025-11-04 13:11:59	2025-11-04 13:11:59	kJ7mY7w1
19	4	<p>Referral to genetics for screening for potential associated malignancies</p>	f	2025-11-04 13:11:59	2025-11-04 13:11:59	wyr1Yrd5
20	4	<p>Intralesional curettage and adjuvant therapy</p>	f	2025-11-04 13:11:59	2025-11-04 13:11:59	y4r8w7NP
26	1055	<p style="text-align: justify">A positive Lhermitte sign.</p>	t	2025-11-04 14:00:13	2025-11-04 14:00:13	Wkr2575Q
27	1055	<p style="text-align: justify">A positive Spurling sign.</p>	f	2025-11-04 14:00:13	2025-11-04 14:00:13	axrqwX2q
28	1055	<p style="text-align: justify">A positive Jackson sign.</p>	f	2025-11-04 14:00:13	2025-11-04 14:00:13	L8M9xrvl
29	1055	<p style="text-align: justify">A positive Lasegue sign.</p>	f	2025-11-04 14:00:13	2025-11-04 14:00:13	vDXQ9X0w
30	1055	<p style="text-align: justify">A positive Hoffmann sign.</p>	f	2025-11-04 14:00:13	2025-11-04 14:00:13	5nModXvm
36	1061	<p style="text-align: justify">Zone 1 at middle and ring finger</p>	f	2025-11-04 20:31:46	2025-11-04 20:31:46	6oMYdrjx
37	1061	<p style="text-align: justify">Zone 2 at middle and ring finger</p>	f	2025-11-04 20:31:46	2025-11-04 20:31:46	mO7nLMGA
38	1061	<p style="text-align: justify">Zone 1 at middle finger and zone 2 at ring finger</p>	f	2025-11-04 20:31:46	2025-11-04 20:31:46	e2rBLXb5
39	1061	<p style="text-align: justify">Zone 2 at middle finger and zone 1 at ring finger</p>	t	2025-11-04 20:31:46	2025-11-04 20:31:46	d1X3Ar5K
40	1061	<p style="text-align: justify">Zone 3 at middle finger and zone 1 at ring finger</p>	f	2025-11-04 20:31:46	2025-11-04 20:31:46	AVr0yMLW
41	1062	<p style="text-align: justify">FDS + FDP tendon</p>	f	2025-11-04 20:31:46	2025-11-04 20:31:46	G57VKrZ8
43	1062	<p style="text-align: justify">FDS + FDP tendon + interdigital nerve</p>	f	2025-11-04 20:31:46	2025-11-04 20:31:46	OD74mr3e
44	1062	<p style="text-align: justify">FDS + FDP tendon+ interdigital nerve ulnar side</p>	t	2025-11-04 20:31:46	2025-11-04 20:31:46	69rDgG7n
45	1062	<p style="text-align: justify">FDS + FDP tendon + neurovascular</p>	f	2025-11-04 20:31:46	2025-11-04 20:31:46	gxXeLLMA
46	1063	<p style="text-align: justify">Interposition of volar plate and or sesamoids</p>	t	2025-11-04 21:14:48	2025-11-04 21:14:48	B6MGOaMz
47	1063	<p style="text-align: justify">Interposition of FPL tendon</p>	f	2025-11-04 21:14:48	2025-11-04 21:14:48	5bMNyvry
48	1063	<p style="text-align: justify">Interposition of central slips</p>	f	2025-11-04 21:14:48	2025-11-04 21:14:48	ParpZ1XK
49	1063	<p style="text-align: justify">Changes of articular joint of 1st MCP</p>	f	2025-11-04 21:14:48	2025-11-04 21:14:48	Gn7bB47N
50	1063	<p style="text-align: justify">Osteophyte at MCP joint</p>	f	2025-11-04 21:14:48	2025-11-04 21:14:48	qx7agRMR
57	1065	<p style="text-align: justify">Short leg cast and discharge with outpatient follow-up</p>	f	2025-11-04 21:22:31	2025-11-04 21:22:31	Qm7v8mMN
58	1065	<p style="text-align: justify">Long leg cast and discharge with outpatient follow-up</p>	f	2025-11-04 21:22:31	2025-11-04 21:22:31	bRXd6lrm
59	1065	<p style="text-align: justify">Percutaneous pinning with casting immobilization</p>	f	2025-11-04 21:22:31	2025-11-04 21:22:31	q6MjwJXR
60	1065	<p style="text-align: justify">Splinting and CT scan of the ankle</p>	t	2025-11-04 21:22:31	2025-11-04 21:22:31	RNrLxqrp
61	1066	<p style="text-align: justify">metacarpophalangeal (MP) joint extension.</p>	f	2025-11-04 21:25:46	2025-11-04 21:25:46	k276Jk7D
62	1066	<p style="text-align: justify">proximal interphalangeal (PIP) joint extension.</p>	t	2025-11-04 21:25:46	2025-11-04 21:25:46	kJ7mEY7w
64	1066	<p style="text-align: justify">proximal interphalangeal (PIP) joint flexion.</p>	f	2025-11-04 21:25:46	2025-11-04 21:25:46	y4r8DwrN
77	1071	<p style="text-align: justify">Pre-injury mobility and cognitive status</p>	t	2025-11-04 21:37:39	2025-11-04 21:37:39	bVXAa6XE
80	1071	<p style="text-align: justify">Length of hospital stay</p>	f	2025-11-04 21:37:39	2025-11-04 21:37:39	6oMYbdXj
81	1072	<p style="text-align: justify">Avascular necrosis</p>	f	2025-11-04 21:39:28	2025-11-04 21:39:28	mO7nqLXG
82	1072	<p style="text-align: justify">Implant cut-out through the femoral head</p>	t	2025-11-04 21:39:28	2025-11-04 21:39:28	e2rBDLXb
42	1062	<p style="text-align: justify">FDS + FDP tendon + interdigital nerve radial side</p>	f	2025-11-04 20:31:46	2025-11-04 20:31:46	dVrR17pP
56	1065	<p style="text-align: justify">Splinting and admit for observation for compartment syndrome</p>	f	2025-11-04 21:22:31	2025-11-04 21:22:31	3L7g8xrn
83	1072	<p style="text-align: justify">Sciatic nerve palsy</p>	f	2025-11-04 21:39:28	2025-11-04 21:39:28	d1X3kAX5
84	1072	<p style="text-align: justify">Deep infection</p>	f	2025-11-04 21:39:28	2025-11-04 21:39:28	AVr0jyXL
85	1072	<p style="text-align: justify">Periprosthetic fracture</p>	f	2025-11-04 21:39:28	2025-11-04 21:39:28	G57VoKrZ
86	1073	<p style="text-align: justify">Avascular necrosis</p>	f	2025-11-04 21:42:39	2025-11-04 21:42:39	dVrRo1Mp
87	1073	<p style="text-align: justify">Implant cut-out through the femoral head</p>	t	2025-11-04 21:42:39	2025-11-04 21:42:39	OD74JmM3
88	1073	<p style="text-align: justify">Sciatic nerve palsy</p>	f	2025-11-04 21:42:39	2025-11-04 21:42:39	69rDVGXn
89	1073	<p style="text-align: justify">Deep infection</p>	f	2025-11-04 21:42:39	2025-11-04 21:42:39	gxXeALXA
90	1073	<p style="text-align: justify">Periprosthetic fracture</p>	f	2025-11-04 21:42:39	2025-11-04 21:42:39	B6MGaa7z
91	1074	<p style="text-align: justify">Ischemic necrosis of femoral head</p>	f	2025-11-04 21:47:43	2025-11-04 21:47:43	5bMN1vry
92	1074	<p style="text-align: justify">Screw cut-through due to osteolysis</p>	t	2025-11-04 21:47:43	2025-11-04 21:47:43	Parp81rK
93	1074	<p style="text-align: justify">Overcompression during fixation</p>	f	2025-11-04 21:47:43	2025-11-04 21:47:43	Gn7bq4XN
94	1074	<p style="text-align: justify">Excessive valgus alignment</p>	f	2025-11-04 21:47:43	2025-11-04 21:47:43	qx7avRrR
95	1074	<p style="text-align: justify">Trochanteric bursitis</p>	f	2025-11-04 21:47:43	2025-11-04 21:47:43	VjryweX6
96	1075	<p style="text-align: justify">Observation and physical therapy</p>	f	2025-11-04 21:47:43	2025-11-04 21:47:43	DnXJOW7A
97	1075	<p style="text-align: justify">Hardware removal only</p>	f	2025-11-04 21:47:43	2025-11-04 21:47:43	ERrOvw7w
98	1075	<p style="text-align: justify">Revision with intramedullary (proximal femoral) nail</p>	f	2025-11-04 21:47:43	2025-11-04 21:47:43	pEXxpPXn
99	1075	<p style="text-align: justify">Conversion to hip arthroplasty</p>	t	2025-11-04 21:47:43	2025-11-04 21:47:43	oP7lK6X3
100	1075	<p style="text-align: justify">Repeat DHS fixation</p>	f	2025-11-04 21:47:43	2025-11-04 21:47:43	69rDVnXn
101	1076	<p style="text-align: justify">a lower complication rate.</p>	f	2025-11-04 21:50:55	2025-11-04 21:50:55	gxXeAEXA
102	1076	<p style="text-align: justify">stronger pinch strength.</p>	f	2025-11-04 21:50:55	2025-11-04 21:50:55	B6MGaK7z
103	1076	<p style="text-align: justify">a higher reoperation rate.</p>	t	2025-11-04 21:50:55	2025-11-04 21:50:55	5bMN1xry
104	1076	<p style="text-align: justify">less pain.</p>	f	2025-11-04 21:50:55	2025-11-04 21:50:55	Parp8QrK
105	1077	<p style="text-align: justify">Continued bracing and steroid injections</p>	f	2025-11-04 21:56:23	2025-11-04 21:56:23	Gn7bqaXN
106	1077	<p style="text-align: justify">Total ankle arthroplasty</p>	f	2025-11-04 21:56:23	2025-11-04 21:56:23	qx7avorR
107	1077	<p style="text-align: justify">Ankle arthroscopy with debridement</p>	f	2025-11-04 21:56:23	2025-11-04 21:56:23	VjrywZX6
108	1077	<p style="text-align: justify">Tibiotalar arthrodesis</p>	t	2025-11-04 21:56:23	2025-11-04 21:56:23	DnXJOv7A
109	1077	<p style="text-align: justify">Subtalar arthrodesis</p>	f	2025-11-04 21:56:23	2025-11-04 21:56:23	ERrOvA7w
111	1078	<p style="text-align: justify">Mechanical axis passes medial to the knee joint center</p>	t	2025-11-04 22:03:06	2025-11-04 22:03:06	\N
112	1078	<p style="text-align: justify">Weakness of the lateral collateral ligament</p>	f	2025-11-04 22:03:06	2025-11-04 22:03:06	\N
113	1078	<p style="text-align: justify">Greater meniscal vascularity in the medial meniscus</p>	f	2025-11-04 22:03:06	2025-11-04 22:03:06	\N
114	1078	<p style="text-align: justify">Medial meniscus is more mobile than the lateral one</p>	f	2025-11-04 22:03:06	2025-11-04 22:03:06	\N
115	1079	<p style="text-align: justify">High tibial osteotomy (HTO)</p>	f	2025-11-04 22:05:14	2025-11-04 22:05:14	\N
116	1079	<p>Unicompartmental knee arthroplasty (UKA)</p>	t	2025-11-04 22:05:14	2025-11-04 22:05:14	\N
117	1079	<p style="text-align: justify">Total knee arthroplasty (TKA)</p>	f	2025-11-04 22:05:14	2025-11-04 22:05:14	\N
118	1079	<p style="text-align: justify">Arthroscopic debridement</p>	f	2025-11-04 22:05:14	2025-11-04 22:05:14	\N
119	1079	<p style="text-align: justify">Distal Femoral Osteotomy (DFO)</p>	f	2025-11-04 22:05:14	2025-11-04 22:05:14	\N
120	1080	<p style="text-align: justify">Tendon subluxation</p>	f	2025-11-04 22:07:14	2025-11-04 22:07:14	\N
121	1080	<p style="text-align: justify">Intersection syndrome</p>	f	2025-11-04 22:07:14	2025-11-04 22:07:14	\N
122	1080	<p style="text-align: justify">Injury to the dorsal radial sensory nerve</p>	f	2025-11-04 22:07:14	2025-11-04 22:07:14	\N
123	1080	<p style="text-align: justify">Tendon injury to the abductor pollicis longus (APL) tendon</p>	f	2025-11-04 22:07:14	2025-11-04 22:07:14	\N
124	1080	<p style="text-align: justify">Unreleased extensor pollicis brevis (EPB) tendon</p>	t	2025-11-04 22:07:14	2025-11-04 22:07:14	\N
125	1081	<p style="text-align: justify">Through the carpal tunnel</p>	f	2025-11-04 22:10:02	2025-11-04 22:10:02	\N
126	1081	<p style="text-align: justify">Across the midpalmar space</p>	f	2025-11-04 22:10:02	2025-11-04 22:10:02	\N
127	1081	<p style="text-align: justify">Communicating with the subcutaneous tissue</p>	f	2025-11-04 22:10:02	2025-11-04 22:10:02	\N
128	1081	<p style="text-align: justify">Superficial to the distal antebrachial fascia</p>	f	2025-11-04 22:10:02	2025-11-04 22:10:02	\N
129	1081	<p style="text-align: justify">Between the fascia of the pronator quadratus and flexor digitorum profundus conjoined tendon sheaths</p>	t	2025-11-04 22:10:02	2025-11-04 22:10:02	\N
130	1082	<p style="text-align: justify">Zone 1 at middle and ring finger</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
131	1082	<p style="text-align: justify">Zone 2 at middle and ring finger</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
132	1082	<p style="text-align: justify">Zone 1 at middle finger and zone 2 at ring finger</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
133	1082	<p style="text-align: justify">Zone 2 at middle finger and zone 1 at ring finger</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
134	1082	<p style="text-align: justify">Zone 3 at middle finger and zone 1 at ring finger</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
135	1083	<p style="text-align: justify">FDS + FDP tendon</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
199	1096	<p style="text-align: justify">IA</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
136	1083	<p style="text-align: justify">FDS + FDP tendon + interdigital nerve radial side</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
137	1083	<p style="text-align: justify">FDS + FDP tendon + interdigital nerve</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
138	1083	<p style="text-align: justify">FDS + FDP tendon + interdigital nerve ulnar side</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
139	1083	<p style="text-align: justify">FDS + FDP tendon + neurovascular</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
140	1084	<p style="text-align: justify">Ischemic necrosis of femoral head</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
141	1084	<p style="text-align: justify">Screw cut-through due to osteolysis</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
142	1084	<p style="text-align: justify">Overcompression during fixation</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
143	1084	<p style="text-align: justify">Excessive valgus alignment</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
144	1084	<p style="text-align: justify">Trochanteric bursitis</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
145	1085	<p style="text-align: justify">Observation and physical therapy</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
146	1085	<p style="text-align: justify">Hardware removal only</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
147	1085	<p style="text-align: justify">Revision with intramedullary (proximal femoral) nail</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
148	1085	<p style="text-align: justify">Conversion to hip arthroplasty</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
149	1085	<p style="text-align: justify">Repeat DHS fixation</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
150	1086	<p style="text-align: justify">IV antibiotics only</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
151	1086	<p style="text-align: justify">Debridement, antibiotics, and implant retention (DAIR)</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
152	1086	<p style="text-align: justify">Two-stage revision arthroplasty</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
153	1086	<p style="text-align: justify">One-stage revision arthroplasty</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
154	1086	<p style="text-align: justify">Explant prosthesis and Fusion</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
155	1087	<p style="text-align: justify">Repeat two-stage revision arthroplasty</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
156	1087	<p style="text-align: justify">Long-term suppressive antibiotics</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
157	1087	<p style="text-align: justify">Knee arthrodesis (fusion)</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
158	1087	<p style="text-align: justify">Above-knee amputation immediately</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
159	1087	<p style="text-align: justify">Re-Debridement Infection</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
160	1088	<p style="text-align: justify">Continue antibiotics for 4 more weeks</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
161	1088	<p style="text-align: justify">Perform repeat debridement</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
162	1088	<p style="text-align: justify">Proceed with second-stage revision arthroplasty</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
163	1088	<p style="text-align: justify">Leave cement spacer permanently</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
164	1088	<p style="text-align: justify">Knee Fusion Surgery</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
165	1089	<p style="text-align: justify">Trigger finger</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
166	1089	<p style="text-align: justify">Camptodactyly</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
167	1089	<p style="text-align: justify">Dupuytren disease</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
168	1089	<p style="text-align: justify">Boutonniere deformity</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
169	1089	<p style="text-align: justify">Stenosing tenosynovitis</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
170	1090	<p style="text-align: justify">Abnormal FDS insertion</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
171	1090	<p style="text-align: justify">Abnormal FDP insertion</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
172	1090	<p style="text-align: justify">Abnormal Terminal band</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
173	1090	<p style="text-align: justify">Hiperlaxity of volar plate</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
174	1090	<p style="text-align: justify">Anteriorisasi lateral band</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
175	1091	<p style="text-align: justify">Right distal femoral and proximal tibia/fibula epiphysiodesis</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
176	1091	<p style="text-align: justify">Right distal femoral epiphysiodesis</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
177	1091	<p style="text-align: justify">Right proximal tibia/fibula epiphysiodesis</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
178	1091	<p style="text-align: justify">Left proximal tibia/fibula epiphysiodesis</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
179	1091	<p style="text-align: justify">lengthening of left tibia</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
180	1092	<p style="text-align: justify">Hip abduction brace</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
181	1092	<p style="text-align: justify">Closed reduction with adductor tenotomy</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
182	1092	<p style="text-align: justify">Open reduction alone</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
183	1092	<p style="text-align: justify">Open reduction with femoral and/or pelvic osteotomy</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
184	1092	<p style="text-align: justify">Skin traction</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
185	1093	<p style="text-align: justify">Osteoporosis</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
186	1093	<p style="text-align: justify">Osteomalacia</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
187	1093	<p style="text-align: justify">Paget's disease</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
188	1093	<p style="text-align: justify">Metastatic bone disease</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
189	1093	<p style="text-align: justify">Rickets</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
190	1094	<p style="text-align: justify">Open reduction and plate fixation</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
191	1094	<p style="text-align: justify">Open reduction and intramedullary rod fixation with casting</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
192	1094	<p style="text-align: justify">Cast immobilization with expected remodeling of the fracture and near-full motion</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
193	1094	<p style="text-align: justify">Cast immobilization, accepting malunion and some dysfunction because surgical treatment has a high rate of nonunion in OI</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
194	1094	<p style="text-align: justify">bone marrow transplantation</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
195	1095	<p style="text-align: justify">Decreased phosphorus, increased serum alkaline phosphatase, normal calcium and vitamin D 25-OH</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
196	1095	<p style="text-align: justify">Decreased phosphorus and calcium, increased serum alkaline phosphatase and increased PTH, decreased 1,25 OH vitamin D</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
197	1095	<p style="text-align: justify">Increased phosphorus, increased calcium, decreased alkaline phosphatase</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
198	1095	<p style="text-align: justify">Increased phosphorus, decreased calcium, increased alkaline phosphatase, and increased PTH</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
200	1096	<p style="text-align: justify">IB</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
201	1096	<p style="text-align: justify">IIA</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
202	1096	<p style="text-align: justify">IIB</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
203	1096	<p style="text-align: justify">III</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
204	1097	<p style="text-align: justify">Bone survey</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
205	1097	<p style="text-align: justify">Contrast MRI of whole femur</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
206	1097	<p style="text-align: justify">CT chest, abdomen, and pelvis</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
207	1097	<p style="text-align: justify">Bone marrow biopsy</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
208	1097	<p style="text-align: justify">Performing a biopsy utilizing fine needle aspiration</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
209	1098	<p style="text-align: justify">Radiation</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
210	1098	<p style="text-align: justify">En bloc excision with structural allograft reconstruction</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
211	1098	<p style="text-align: justify">Extended Curettage with application of demineralized bone matrix and iliac crest bone marrow aspirate</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
212	1098	<p style="text-align: justify">En bloc excision with wrist fusion using vascularized fibular autograft</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
213	1098	<p style="text-align: justify">Extended Curettage, application of liquid nitrogen, polymethylmethacrylate</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
214	1099	<p style="text-align: justify">The diagnosis is giant cell tumor. Bone scan is necessary.</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
215	1099	<p style="text-align: justify">The diagnosis is giant cell tumor. Chest radiograph and Lower leg contrast MRI are necessary.</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
216	1099	<p style="text-align: justify">The diagnosis is giant cell tumor. No further staging study is necessary.</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
217	1099	<p style="text-align: justify">The diagnosis is giant cell-rich osteosarcoma. Chest radiograph and lower leg contrast MRI are necessary.</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
218	1099	<p style="text-align: justify">The diagnosis is giant cell-rich osteosarcoma. CT of the chest, abdomen, and pelvis is necessary.</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
219	1100	<p style="text-align: justify">RANK ligand action on neoplastic cells</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
220	1100	<p style="text-align: justify">RANK ligand action on osteoclastic cells</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
221	1100	<p style="text-align: justify">RANK action on neoplastic cells</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
222	1100	<p style="text-align: justify">Osteoprotegrin action on osteoclastic cells</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
223	1100	<p style="text-align: justify">RANK action on osteoblastic cells</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
224	1101	<p style="text-align: justify">Adductor releases</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
225	1101	<p style="text-align: justify">Adductor releases and psoas lengthenings</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
226	1101	<p style="text-align: justify">Varus derotational osteotomies</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
227	1101	<p style="text-align: justify">Pelvic osteotomies</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
228	1101	<p style="text-align: justify">Varus derotational osteotomies with pelvic osteotomies</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
229	1102	<p style="text-align: justify">The patient has a nonprogressive injury to his brain, and he will likely require multiple orthopaedic surgeries in the future as a result of muscle imbalance</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
230	1102	<p style="text-align: justify">The patient has an absent dystrophin protein and will likely require a wheelchair by the age of 15 and will die of cardiorespiratory problems by the age of 20</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
231	1102	<p style="text-align: justify">The child will have a progressive loss of alpha-motor neurons in anterior horn of spinal cord. He will have difficulty walking, but will be able to sit independently and will likely live into the fifth decade of life</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
232	1102	<p style="text-align: justify">The child will have a progressive loss of alpha-motor neurons in anterior horn of spinal cord and will unlikely live past the age of two</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
233	1102	<p style="text-align: justify">The child will likely go on to develop a cavus foot and hammer toes, but he should live a full healthy life</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
234	1103	<p style="text-align: justify">Medial cord</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
235	1103	<p style="text-align: justify">Lateral cord</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
236	1103	<p style="text-align: justify">Posterior cord</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
237	1103	<p style="text-align: justify">Superior trunk</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
238	1103	<p style="text-align: justify">Middle trunk</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
239	1104	<p style="text-align: justify">Submuscular plating</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
240	1104	<p style="text-align: justify">Immediate hip spica casting</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
241	1104	<p style="text-align: justify">Flexible intramedullary nailing</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
242	1104	<p style="text-align: justify">Locked intramedullary nailing with trochanteric entry</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
243	1104	<p style="text-align: justify">Locking plating</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
244	1105	<p style="text-align: justify">the rate of revision surgery</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
245	1105	<p style="text-align: justify">surgical time</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
246	1105	<p style="text-align: justify">length of stay</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
247	1105	<p style="text-align: justify">the rate of pulmonary complications</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
248	1105	<p style="text-align: justify">mobilitation time</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
249	1106	<p style="text-align: justify">Patient sex</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
250	1106	<p style="text-align: justify">Duration of patient tobacco use</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
251	1106	<p style="text-align: justify">Intra-articular fracture severity</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
252	1106	<p style="text-align: justify">Axial versus torsional mechanisms</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
253	1106	<p style="text-align: justify">Diaphysis fracture</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
254	1107	<p style="text-align: justify">Open reduction and internal fixation (ORIF)</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
255	1107	<p style="text-align: justify">a mallet splint</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
256	1107	<p style="text-align: justify">Repair of the terminal tendon</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
257	1107	<p style="text-align: justify">Arthrodesis</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
258	1107	<p style="text-align: justify">Arthroplasty</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
259	1108	<p style="text-align: justify">Superficial branch of ulnar nerve</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
260	1108	<p style="text-align: justify">Deep branch of ulnar nerve</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
261	1108	<p style="text-align: justify">Nerve of Henle</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
262	1108	<p style="text-align: justify">Common digital nerve to the fourth web</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
263	1108	<p style="text-align: justify">Palmar cutaneous branch of the median nerve</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
264	1109	<p style="text-align: justify">Continued skin traction</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
265	1109	<p style="text-align: justify">Skeletal traction of both femurs</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
266	1109	<p style="text-align: justify">External fixation of both femurs</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
267	1109	<p style="text-align: justify">Intramedullary nailing of one femur and external fixation and delayed nailing for the other femur</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
268	1109	<p style="text-align: justify">Intramedullary nailing of both femurs</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
269	1110	<p style="text-align: justify">Inlet and outlet views of the pelvis to better delineate the injury</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
270	1110	<p style="text-align: justify">Angiograph</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
271	1110	<p style="text-align: justify">Laparotomy</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
272	1110	<p style="text-align: justify">Open reduction and internal fixation of the pelvis</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
273	1110	<p style="text-align: justify">Placement of a pelvic binder around the patient</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
274	1111	<p style="text-align: justify">Posterior column with articular impaction and a free fragment</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
275	1111	<p style="text-align: justify">Anterior column with articular impaction</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
276	1111	<p style="text-align: justify">Posterior wall with an intra-articular fragment</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
277	1111	<p style="text-align: justify">Posterior wall with articular impaction and a free intra-articular fragment</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
278	1111	<p style="text-align: justify">Posterior wall with articular impaction</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
279	1112	<p style="text-align: justify">Cast removal and measurement of carpal canal pressure</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
280	1112	<p style="text-align: justify">Immediate carpal tunnel release and pinning of the fracture</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
281	1112	<p style="text-align: justify">Continued observation</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
282	1112	<p style="text-align: justify">Surgical reduction and pinning of the fracture</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
283	1112	<p style="text-align: justify">Electromyography/nerve conduction velocity studies</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
284	1113	<p style="text-align: justify">Radiography-guided steroid injection followed by total hip arthroplasty 6 weeks later</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
285	1113	<p style="text-align: justify">Total hip arthroplasty</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
286	1113	<p style="text-align: justify">Physical therapy</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
287	1113	<p style="text-align: justify">Referral to her spine surgeon</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
288	1113	<p style="text-align: justify">Bipolar hip hemiarthroplasty</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
289	1114	<p style="text-align: justify">Proximal tibial osteotomy</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
290	1114	<p style="text-align: justify">Distal femoral osteotomy</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
291	1114	<p style="text-align: justify">Lateral unicompartmental arthroplasty</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
292	1114	<p style="text-align: justify">Unicompartmental knee arthroplasty</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
293	1114	<p style="text-align: justify">Total knee arthroplasty</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
294	1115	<p style="text-align: justify">a prostaglandin-secreting prostate metastasis</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
295	1115	<p style="text-align: justify">inhibition of osteoclastic function</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
296	1115	<p style="text-align: justify">right L4 radiculopathy secondary to prostate metastasis</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
297	1115	<p style="text-align: justify">direct inhibition of osteoclast prenylation</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
298	1116	<p style="text-align: justify">Observation</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
299	1116	<p style="text-align: justify">Valgus-inducing knee-ankle-foot orthotic bracing</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
300	1116	<p style="text-align: justify">Bilateral proximal tibia and fibula epiphysiodesis</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
301	1116	<p style="text-align: justify">Immediate bilateral tibia and fibular valgus osteotomies with gradual correction with external fixators</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
302	1116	<p style="text-align: justify">Immediate bilateral tibia and fibular valgus osteotomies with acute correction and internal fixation</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
303	1117	<p style="text-align: justify">Planktonic</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
304	1117	<p style="text-align: justify">Sessile</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
305	1117	<p style="text-align: justify">Maturation</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
306	1117	<p style="text-align: justify">Metabolic</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
307	1117	<p style="text-align: justify">Dispersion</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
308	1118	<p style="text-align: justify">Stage 2A</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
309	1118	<p style="text-align: justify">Stage 1B</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
310	1118	<p style="text-align: justify">Stage 1C</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
311	1118	<p style="text-align: justify">Stage 3B</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
312	1118	<p style="text-align: justify">Stage 4C</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
313	1119	<p style="text-align: justify">Involucrum</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
314	1119	<p style="text-align: justify">Sequestrum</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
315	1119	<p style="text-align: justify">Callus</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
316	1119	<p style="text-align: justify">Avascular necrosis</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
317	1119	<p style="text-align: justify">Malignant bone tumor</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
318	1120	<p style="text-align: justify">Aspiration of the knee to rule out infection</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
319	1120	<p style="text-align: justify">Empiric antibiotic treatment</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
320	1120	<p style="text-align: justify">Open incision and drainage of osteomyelitis</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
321	1120	<p style="text-align: justify">Immobilization in a splint for 3 weeks</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
322	1120	<p style="text-align: justify">Immobilization in long-leg cast for 12 weeks</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
323	1121	<p style="text-align: justify">Diphosphonate or denosumab and observation with cementoplasty for refractory pain</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
324	1121	<p style="text-align: justify">Chemotherapy and wide surgical resection</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
325	1121	<p style="text-align: justify">Wide surgical resection alone</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
326	1121	<p style="text-align: justify">Extended intralesional curettage and grafting</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
327	1121	<p style="text-align: justify">Combination of Chemotherapy and targeted radiotherapy</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
328	1122	<p style="text-align: justify">A 67-year-old man with an osteoporotic femoral neck fracture</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
329	1122	<p style="text-align: justify">A 71-year-old man with a 15% 10-year probability of a major osteoporosis-related fracture based on the US-adapted World Health Organization (WHO) algorithm</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
330	1122	<p style="text-align: justify">A 77-year-old woman with a T score of 0.8 and a compression fracture following a motor vehicle collision</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
331	1122	<p style="text-align: justify">An 82-year-old woman with a T score of 1.3</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
332	1122	<p style="text-align: justify">A 61-year-old woman with Z-Score of 0.5</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
333	1123	<p style="text-align: justify">Denosumab</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
334	1123	<p style="text-align: justify">Alendronate</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
335	1123	<p style="text-align: justify">Abaloparatide</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
336	1123	<p style="text-align: justify">Teriparatide</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
337	1123	<p style="text-align: justify">Strontium ranelate</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
338	1124	<p style="text-align: justify">Aerobic, Gram-positive rod</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
339	1124	<p style="text-align: justify">Anaerobic, Gram-positive coccus</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
340	1124	<p style="text-align: justify">Anaerobic, Gram-negative rod</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
341	1124	<p style="text-align: justify">Catalase positive</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
342	1124	<p style="text-align: justify">Possible cause botulism</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
343	1125	<p style="text-align: justify">Tachycardia</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
344	1125	<p style="text-align: justify">Bradycardia</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
345	1125	<p style="text-align: justify">Decreased cardiac output</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
346	1125	<p style="text-align: justify">Vasodilation</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
347	1125	<p style="text-align: justify">Warm dry skin</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
348	1126	<p style="text-align: justify">HR >110</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
349	1126	<p style="text-align: justify">Bilateral pulmonary contusions seen on chest radiograph</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
350	1126	<p style="text-align: justify">SBP = 90mmHg</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
351	1126	<p style="text-align: justify">Unilateral femur fracture</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
352	1126	<p style="text-align: justify">Lactate = 2.5 mMol/L</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
353	1127	<p style="text-align: justify">Weakness of hip flexion</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
354	1127	<p style="text-align: justify">Weakness of ankle dorsiflexion</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
355	1127	<p style="text-align: justify">Numbness of the medial thigh</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
356	1127	<p style="text-align: justify">Numbness of the lateral thigh</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
357	1127	<p style="text-align: justify">Numbness of the perineum</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
358	1128	<p style="text-align: justify">Cephalosporin</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
359	1128	<p style="text-align: justify">Cephalosporin and aminoglycoside</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
360	1128	<p style="text-align: justify">Cephalosporin and penicillin</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
361	1128	<p style="text-align: justify">Cephalosporin and vancomyacin</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
362	1128	<p style="text-align: justify">Cephalosporin, aminoglycoside, and penicillin</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
363	1129	<p style="text-align: justify">Musculocutaneous</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
364	1129	<p style="text-align: justify">Axillary</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
365	1129	<p style="text-align: justify">Radial</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
366	1129	<p style="text-align: justify">Median</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
367	1129	<p style="text-align: justify">Ulnar</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
368	1130	<p style="text-align: justify">Reamed intramedullary nail</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
369	1130	<p style="text-align: justify">Unreamed intramedullary nail</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
370	1130	<p style="text-align: justify">Percutaneous plate fixation</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
371	1130	<p style="text-align: justify">Skeletal traction</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
372	1130	<p style="text-align: justify">External fixation</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
373	1131	<p style="text-align: justify">Posttraumatic arthritis of the subtalar joint</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
374	1131	<p style="text-align: justify">Posttraumatic arthritis of the ankle joint</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
375	1131	<p style="text-align: justify">Malunion of talus</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
376	1131	<p style="text-align: justify">Osteonecrosis of the talus</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
377	1131	<p style="text-align: justify">Complex regional pain syndrome</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
378	1132	<p style="text-align: justify">Superficial debridement and IV antibiotics</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
379	1132	<p style="text-align: justify">Superficial debridement and oral antibiotics</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
380	1132	<p style="text-align: justify">Immediate wound closure and oral antibiotics</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
381	1132	<p style="text-align: justify">Exploration of the joint and IV antibiotics</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
382	1132	<p style="text-align: justify">Superficial debridement, secondary closure, and IV antibiotics</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
383	1133	<p style="text-align: justify">Ca++, alkaline phosphatase, and vitamin D levels</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
384	1133	<p style="text-align: justify">CT to rule out occult fracture</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
385	1133	<p style="text-align: justify">USG</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
386	1133	<p style="text-align: justify">Repeated X-ray after 2 week</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
387	1133	<p style="text-align: justify">Skeletal survey</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
388	1134	<p style="text-align: justify">Medial gastrocnemius muscle flap</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
389	1134	<p style="text-align: justify">Splint-thickness skin graft</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
390	1134	<p style="text-align: justify">Cross-leg gastrocnemius flap</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
391	1134	<p style="text-align: justify">Soleus muscle flap</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
392	1134	<p style="text-align: justify">Free muscle transfer</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
393	1135	<p style="text-align: justify">Insert a small-diameter threaded pin at a different angle through the locking hole</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
394	1135	<p style="text-align: justify">Insert a screw through the hole either anterior or posterior to the intramedullary nail locking hole</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
395	1135	<p style="text-align: justify">Leave only one distal screw; this will provide adequate fixation</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
396	1135	<p style="text-align: justify">Exchange the nail for one either longer or shorter and relock at a new level</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
397	1135	<p style="text-align: justify">Insert methylmethacrylate cement into the hole and redrill when the cement hardens</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
398	1136	<p style="text-align: justify">Achilles tendon lengthening</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
399	1136	<p style="text-align: justify">Ankle-foot orthosis with dorsiflexion assist</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
400	1136	<p style="text-align: justify">Posterior tibial tendon transfer to the cuboid</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
401	1136	<p style="text-align: justify">Nerve grafting</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
402	1136	<p style="text-align: justify">Anterior tibial tendon transfer to the cuboid</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
403	1137	<p style="text-align: justify">2.0 mm</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
404	1137	<p style="text-align: justify">2.5 mm</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
405	1137	<p style="text-align: justify">3.0 mm</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
406	1137	<p style="text-align: justify">3.5 mm</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
407	1137	<p style="text-align: justify">4.0 mm</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
408	1138	<p style="text-align: justify">Greater ultimate clinical arc of elbow motion</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
409	1138	<p style="text-align: justify">Lower revision rate</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
410	1138	<p style="text-align: justify">Lower incidence of ulnar nerve injury</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
411	1138	<p style="text-align: justify">Greater experimental biomechanical stability</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
412	1138	<p style="text-align: justify">More anatomic fracture reduction</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
413	1139	<p style="text-align: justify">After repair meniscus, patient should be non weightbearing for 6 weeks</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
414	1139	<p style="text-align: justify">The meniscus repair is considered if the tear is in the inner side of the meniscus</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
415	1139	<p style="text-align: justify">Repair meniscus can be performed only for tear in the lateral meniscus</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
416	1139	<p style="text-align: justify">Meniscus repair can not be performed at the same time with ACL reconstruction</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
417	1139	<p style="text-align: justify">Patients above 30 years of age may not have repaired meniscus</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
418	1140	<p style="text-align: justify">Malreduced sacroiliac joint</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
419	1140	<p style="text-align: justify">Residual displacement causing a leg-length discrepancy of less than 1.0 cm</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
420	1140	<p style="text-align: justify">Fracture nonunion</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
421	1140	<p style="text-align: justify">Genitourinary dysfunction</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
422	1140	<p style="text-align: justify">Persistent neurologic deficit</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
423	1141	<p style="text-align: justify">non-surgical with bedrest</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
424	1141	<p style="text-align: justify">non-surgical with plaster jacket followed by bracing</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
425	1141	<p style="text-align: justify">non-surgical with bracing only</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
426	1141	<p style="text-align: justify">surgical anterior instrumentation</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
427	1141	<p style="text-align: justify">surgical posterior instrumentation</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
428	1142	<p style="text-align: justify">13</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
429	1142	<p style="text-align: justify">18</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
430	1142	<p style="text-align: justify">22</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
431	1142	<p style="text-align: justify">27</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
432	1142	<p style="text-align: justify">35</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
433	1143	<p style="text-align: justify">Intramedullary nailing of bilateral femurs, and intramedullary nailing of the left humerus</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
434	1143	<p style="text-align: justify">External fixation of bilateral femurs, and splinting of the left humerus</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
435	1143	<p style="text-align: justify">External fixation of bilateral femurs, and open reduction and internal fixation of the left humerus</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
436	1143	<p style="text-align: justify">Intramedullary nailing of bilateral femurs, and open reduction and internal fixation of the left humerus</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
437	1143	<p style="text-align: justify">External fixation of bilateral femurs, and intramedullary nailing of the left humerus</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
438	1144	<p style="text-align: justify">Myelography with CT</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
439	1144	<p style="text-align: justify">Spinal cord-evoked potentials</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
440	1144	<p style="text-align: justify">Repeat physical examinations</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
441	1144	<p style="text-align: justify">MRI</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
442	1144	<p style="text-align: justify">Electromyography and nerve conduction velocity studies</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
443	1145	<p style="text-align: justify">adequate surgical debridement</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
444	1145	<p style="text-align: justify">adequate antibiotic therapy</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
445	1145	<p style="text-align: justify">although stability of internal fixation is critical, deep infection can occur with inadequate debridement</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
446	1145	<p style="text-align: justify">do not remove all the necrotic tissue</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
447	1145	<p style="text-align: justify">prompt soft-tissue coverage will salvage contamined</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
448	1146	<p style="text-align: justify">Alteration of bacterial cell membrane permeability</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
449	1146	<p style="text-align: justify">Inhibition of bacterial protein synthesis</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
450	1146	<p style="text-align: justify">Inhibition of bacterial metabolism</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
451	1146	<p style="text-align: justify">Inhibition of bacterial cell wall synthesis</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
452	1146	<p style="text-align: justify">Interference with bacterial nucleic acid synthesis or activity</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
453	1147	<p style="text-align: justify">Charcot's foot changes</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
454	1147	<p style="text-align: justify">Decreased ABPI scores</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
455	1147	<p style="text-align: justify">Decreased oxygen tension levels</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
456	1147	<p style="text-align: justify">Diabetic foot ulceration</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
457	1147	<p style="text-align: justify">HBA1C levels</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
458	1148	<p style="text-align: justify">Tarsal tunnel release</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
459	1148	<p style="text-align: justify">Gastrocnemius recession</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
460	1148	<p style="text-align: justify">Heel spur removal</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
461	1148	<p style="text-align: justify">Achilles tendon lengthening</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
462	1148	<p style="text-align: justify">Anterior ankle decompression</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
463	1149	<p style="text-align: justify">Joint aspiration and synovial fluid analysis</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
464	1149	<p style="text-align: justify">AP and frog-lateral pelvis radiographs</p>	t	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
465	1149	<p style="text-align: justify">Non-steroidal anti-inflammatory drugs and physical therapy for tibial tubercle apophysitis</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
466	1149	<p style="text-align: justify">MRI of the left knee for evaluation of stress fracture</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
467	1149	<p style="text-align: justify">Protected weight bearing with crutches and repeat evaluation in one week</p>	f	2025-11-04 22:11:39	2025-11-04 22:11:39	\N
468	1150	<p style="text-align: justify">Positive in 98% of rheumatoid arthritis case</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
469	1150	<p style="text-align: justify">Should never be checked in synovitis stage</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
470	1150	<p style="text-align: justify">It is a specific test to diagnose rheumatoid arthritis</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
471	1150	<p style="text-align: justify">RF is low in Systemic Lupus Eritematous patients</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
472	1150	<p style="text-align: justify">RF is related with B-cell activation that produce anti-IgG autoantibodies</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
473	1151	<p style="text-align: justify">Anconeus</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
474	1151	<p style="text-align: justify">Extensor carpi radialis brevis</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
475	1151	<p style="text-align: justify">Distal biceps brachii</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
476	1151	<p style="text-align: justify">Extensor pollicis longus</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
477	1151	<p style="text-align: justify">Supinator</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
478	1152	<p style="text-align: justify">Freiberg's infarction</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
479	1152	<p style="text-align: justify">Plantar fasciitis</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
480	1152	<p style="text-align: justify">Navicular stress fracture</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
481	1152	<p style="text-align: justify">Anterior tarsal tunnel syndrome</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
482	1152	<p style="text-align: justify">The first branch of the lateral plantar nerve entrapment</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
483	1153	<p style="text-align: justify">No effect</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
484	1153	<p style="text-align: justify">Index finger weakness</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
485	1153	<p style="text-align: justify">Index metacarpophalangeal hyperextension</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
486	1153	<p style="text-align: justify">Index metacarpophalangeal hyperflexion</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
487	1153	<p style="text-align: justify">Index metacarpophalangeal ulnar deviation</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
488	1154	<p style="text-align: justify">A positive Jackson sign</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
489	1154	<p style="text-align: justify">A positive Lasegue sign</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
490	1154	<p style="text-align: justify">A positive Lhermitte sign</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
491	1154	<p style="text-align: justify">A positive Hoffmann sign</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
492	1154	<p style="text-align: justify">A positive Spurling sign</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
493	1155	<p style="text-align: justify">Elastin</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
494	1155	<p style="text-align: justify">Fibrillin</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
495	1155	<p style="text-align: justify">Collagen type 1</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
496	1155	<p style="text-align: justify">Collagen type 2</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
497	1155	<p style="text-align: justify">Fibroblast Growth Factor Receptor 3 (FGFR3)</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
498	1156	<p style="text-align: justify">Clawing of small and ring finger</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
499	1156	<p style="text-align: justify">An abnormal sensation of the dorsal ulnar hand</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
500	1156	<p style="text-align: justify">Abnormal sensation in the volar ring and small fingers</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
501	1156	<p style="text-align: justify">A weakness of the interosseous muscles</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
502	1156	<p style="text-align: justify">A positive Froment sign</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
503	1157	<p style="text-align: justify">Use of allograft (instead of autograft)</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
504	1157	<p style="text-align: justify">Fusion at the C3-C4 level</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
505	1157	<p style="text-align: justify">Sagittal alignment</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
506	1157	<p style="text-align: justify">Performance of an uninstrumented fusion (ie, no plate and screws)</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
507	1157	<p style="text-align: justify">History of diabetes mellitus and tobacco use</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
508	1158	<p style="text-align: justify">Hip-knee mechanical axis</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
509	1158	<p style="text-align: justify">anteroposterior axis</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
510	1158	<p style="text-align: justify">femoral intramedullary axis</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
511	1158	<p style="text-align: justify">tibial intramedullary axis</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
512	1158	<p style="text-align: justify">posterior condylar axis</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
513	1159	<p style="text-align: justify">cosmetic appearance</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
514	1159	<p style="text-align: justify">split-size shoe reqirements</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
515	1159	<p style="text-align: justify">an intermetatarsal angle of greater than 150 between the first and second metatarsals</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
516	1159	<p style="text-align: justify">symptoms that persist despite nonsurgical management</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
517	1159	<p style="text-align: justify">arthritic changes in the first metatarsophalangeal joint</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
518	1160	<p style="text-align: justify">Obturator externus</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
519	1160	<p style="text-align: justify">Piriformis</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
520	1160	<p style="text-align: justify">Psoas</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
521	1160	<p style="text-align: justify">Rectus femoris</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
522	1160	<p style="text-align: justify">Gemelus superior</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
523	1161	<p style="text-align: justify">Immediate hip arthroplasty</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
524	1161	<p style="text-align: justify">Immediate bilateral sacroiliac joint aspiration and culture</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
525	1161	<p style="text-align: justify">Anesthetic injections in both sacroiliac joints</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
526	1161	<p style="text-align: justify">Anti-inflammatory medications, physical therapy, and HLA-B27 testing</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
527	1161	<p style="text-align: justify">Sacroiliac fusion with plate fixation</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
528	1162	<p style="text-align: justify">Pain when carrying heavy objects with the elbow in extension</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
529	1162	<p style="text-align: justify">Pain at a mid-arc range of motion</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
530	1162	<p style="text-align: justify">Motion loss greater than 30 degrees</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
531	1162	<p style="text-align: justify">Ulnar neuritis</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
532	1162	<p style="text-align: justify">Pain even without motion</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
533	1163	<p style="text-align: justify">Marginally decreased bone density</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
534	1163	<p style="text-align: justify">Osteopenia</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
535	1163	<p style="text-align: justify">Bone within normal limits for an elderly individual</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
536	1163	<p style="text-align: justify">Osteoporosis</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
537	1163	<p style="text-align: justify">Metastatic bone disease</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
538	1164	<p style="text-align: justify">Multilevel interbody fusion</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
539	1164	<p style="text-align: justify">Teriparatide injection</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
540	1164	<p style="text-align: justify">Iliac crest bone graft</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
541	1164	<p style="text-align: justify">Augmentation of pedicle screws with polymethylmethacrylate (PMMA)</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
542	1165	<p style="text-align: justify">Impaired ability of the liver to hydroxylate cholecalciferol</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
543	1165	<p style="text-align: justify">Impaired absorption of calcium by the gastrointestinal (GI) tract</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
544	1165	<p style="text-align: justify">Impaired ability of the kidneys to hydroxylate cholecalciferol</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
545	1165	<p style="text-align: justify">Impaired parathyroid hormone (PTH) production by the parathyroid glands</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
546	1166	<p style="text-align: justify">Enhanced end-bearing</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
547	1166	<p style="text-align: justify">Early prosthetic fitting</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
548	1166	<p style="text-align: justify">Decreased surgical morbidity</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
549	1166	<p style="text-align: justify">Immediate weight bearing</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
550	1166	<p style="text-align: justify">Fibular abduction</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
551	1167	<p style="text-align: justify">Secondary infertility</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
552	1167	<p style="text-align: justify">Secondary calcium deficiency</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
553	1167	<p style="text-align: justify">Rebound uterine hypertrophy</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
554	1167	<p style="text-align: justify">Irreversible loss of bone mineral density</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
555	1167	<p style="text-align: justify">Functional hyperthyroidism</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
556	1168	<p style="text-align: justify">Lower infection risk</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
557	1168	<p style="text-align: justify">Lower graft rupture rate</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
558	1168	<p style="text-align: justify">Better incorporation of the graft material</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
559	1168	<p style="text-align: justify">Lower long-term risk for arthritis</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
560	1168	<p style="text-align: justify">Lack of donor-site morbidity</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
561	1169	<p style="text-align: justify">Limb preservation</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
562	1169	<p style="text-align: justify">Shortening of bone</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
563	1169	<p style="text-align: justify">Delayed repair of nerves</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
564	1169	<p style="text-align: justify">Immediate arterial and venous repair</p>	f	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
565	1169	<p style="text-align: justify">Angiograms</p>	t	2025-11-04 22:11:40	2025-11-04 22:11:40	\N
566	1170	<p style="text-align: justify">Through the carpal tunnel</p>	f	2025-11-04 22:12:19	2025-11-04 22:12:19	\N
567	1170	<p style="text-align: justify">Across the midpalmar space</p>	f	2025-11-04 22:12:19	2025-11-04 22:12:19	\N
568	1170	<p style="text-align: justify">Communicating with the subcutaneous tissue</p>	f	2025-11-04 22:12:19	2025-11-04 22:12:19	\N
569	1170	<p style="text-align: justify">Superficial to the distal antebrachial fascia</p>	f	2025-11-04 22:12:19	2025-11-04 22:12:19	\N
570	1170	<p style="text-align: justify">Between the fascia of the pronator quadratus and flexor digitorum profundus conjoined tendon sheaths</p>	t	2025-11-04 22:12:19	2025-11-04 22:12:19	\N
\.


--
-- Data for Name: attachables; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.attachables (attachment_id, attachable_id, attachable_type) FROM stdin;
e638f3b9-006a-4021-8c26-425fe3da7bc8	1	App\\Models\\Exams\\Item
48eb006d-20c2-4d4a-81b5-374ebf5f7c27	1	App\\Models\\Exams\\Item
520f849f-42d5-4df2-8ee6-b85299a718ee	2	App\\Models\\Exams\\Item
8512c878-0158-40a0-852f-f2ca436963fa	2	App\\Models\\Exams\\Item
f7b7e186-56d0-4ea8-a420-56d9b16244a9	2	App\\Models\\Exams\\Item
5f74bb4e-0aeb-478a-80a7-178f3522907a	3	App\\Models\\Exams\\Item
a2d85554-f6a5-41f0-acfa-a5b48af6cad1	724	App\\Models\\Exams\\Item
dccd2ebd-68f9-41ab-9507-770a6b98d951	725	App\\Models\\Exams\\Item
3ef1a491-9302-416a-8fc3-c651ba1320ca	726	App\\Models\\Exams\\Item
6fe98861-d9d2-46c2-a425-f109152679cd	728	App\\Models\\Exams\\Item
53a801af-95ef-4c6a-9a1f-cf7159d68846	730	App\\Models\\Exams\\Item
c9b1ae39-d999-4019-b771-eab073782e44	733	App\\Models\\Exams\\Item
d93c222b-17f7-4556-bfcc-fbfec6f51691	735	App\\Models\\Exams\\Item
\.


--
-- Data for Name: attachments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.attachments (id, type, uploaded_by, title, path, mime, description, options, created_at, updated_at, client_id) FROM stdin;
e638f3b9-006a-4021-8c26-425fe3da7bc8	attachment	1	\N	attachments/VxgOjk3W-jOqoGMG7xD.png	png	\N	\N	2025-11-04 13:03:44	2025-11-04 13:03:44	3
48eb006d-20c2-4d4a-81b5-374ebf5f7c27	attachment	1	\N	attachments/VxgOjk3W-mgUjQvu73w.png	png	\N	\N	2025-11-04 13:03:50	2025-11-04 13:03:50	3
520f849f-42d5-4df2-8ee6-b85299a718ee	attachment	1	\N	attachments/qzKoYkDo-GITJLRVB6F.png	png	\N	\N	2025-11-04 13:08:32	2025-11-04 13:08:32	3
8512c878-0158-40a0-852f-f2ca436963fa	attachment	1	\N	attachments/qzKoYkDo-GhSzNWtCsa.png	png	\N	\N	2025-11-04 13:08:37	2025-11-04 13:08:37	3
f7b7e186-56d0-4ea8-a420-56d9b16244a9	attachment	1	\N	attachments/qzKoYkDo-zUbJ4hiRy7.png	png	\N	\N	2025-11-04 13:08:44	2025-11-04 13:08:44	3
5f74bb4e-0aeb-478a-80a7-178f3522907a	attachment	1	\N	attachments/YRgyGB68-wmY9h91aWl.png	png	\N	\N	2025-11-04 13:12:38	2025-11-04 13:12:38	3
a2d85554-f6a5-41f0-acfa-a5b48af6cad1	attachment	1	\N	attachments/7pKAdGg3-B40RTFAbpz.jpg	jpg	\N	\N	2025-11-04 21:29:09	2025-11-04 21:29:09	3
dccd2ebd-68f9-41ab-9507-770a6b98d951	attachment	1	\N	attachments/AdknOGgR-fHRmxZc5FI.jpg	jpg	\N	\N	2025-11-04 21:30:44	2025-11-04 21:30:44	3
3ef1a491-9302-416a-8fc3-c651ba1320ca	attachment	1	\N	attachments/QnB0X4BA-1mlbUPk5Xj.png	png	\N	\N	2025-11-04 21:31:03	2025-11-04 21:31:03	3
6fe98861-d9d2-46c2-a425-f109152679cd	attachment	1	\N	attachments/j0gxEGKM-IuxXxS1RsG.png	png	\N	\N	2025-11-04 21:31:20	2025-11-04 21:31:20	3
53a801af-95ef-4c6a-9a1f-cf7159d68846	attachment	1	\N	attachments/j4B4XQBz-LOwGkzPLKv.png	png	\N	\N	2025-11-04 21:35:57	2025-11-04 21:35:57	3
c9b1ae39-d999-4019-b771-eab073782e44	attachment	1	\N	attachments/rxKX5xBm-uq2PZ68rVC.png	png	\N	\N	2025-11-04 21:49:09	2025-11-04 21:49:09	3
d93c222b-17f7-4556-bfcc-fbfec6f51691	attachment	1	\N	attachments/4Og99Rg6-8DISkjKMYn.png	png	\N	\N	2025-11-04 22:01:27	2025-11-04 22:01:27	3
\.


--
-- Data for Name: attempt_question; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.attempt_question (id, attempt_id, question_id, answer_id, answer_hash, answer, is_correct, score, created_at, updated_at) FROM stdin;
3	2	4	18	kJ7mY7w1	<p>Educate the family about the benign nature of the condition and prescribe NSAIDs until the pain diminishes</p>	f	0.00	2025-11-04 20:18:21	2025-11-04 20:18:21
4	2	1055	29	vDXQ9X0w	<p style="text-align: justify">A positive Lasegue sign.</p>	f	0.00	2025-11-04 20:18:24	2025-11-04 20:37:34
1	2	1	1	gxXeLMAR	<p>Emergent surgery, including open carpal tunnel release, open reduction of the perilunate dislocation, repair of the scapholunate ligament, and intercarpal pinning</p>	t	100.00	2025-11-04 19:21:59	2025-11-04 20:19:13
2	2	3	11	oP7l6M3d	<p>Osteosarcoma</p>	f	0.00	2025-11-04 20:18:17	2025-11-04 20:37:25
\.


--
-- Data for Name: attempts; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.attempts (id, attempted_by, exam_id, delivery_id, ip_address, started_at, ended_at, extra_minute, score, progress, penalty, created_at, updated_at, finish_scoring, client_id, finished_at, hash) FROM stdin;
2	1	43	152	114.10.47.246	2025-11-04 17:41:57	2025-11-04 20:37:39	0	25.00	100	0	2025-11-04 17:41:57	2025-11-04 20:37:39	f	3	\N	x3LxqWX8
\.


--
-- Data for Name: categories; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.categories (id, type, code, parent, name, description, created_at, updated_at, client_id, hash) FROM stdin;
1	disease-group	\N	0	Unspecified	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	8rlmZkE7
2	disease-group	\N	0	Congenital	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	DolLWMVg
3	disease-group	\N	0	Infection	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	D4lGKlBy
4	disease-group	\N	0	Tumor	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	O4Pp4PeL
5	disease-group	\N	0	Injury/Trauma	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	eok6KPEw
6	disease-group	\N	0	Metabolic	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	m4kaQk91
7	disease-group	\N	0	Inflammatory	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	JBMJXkZb
8	disease-group	\N	0	Degenerative	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	qZPXaM2e
9	disease-group	\N	0	Neuromuscular	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	31l9Nk9L
10	disease-group	\N	0	Basic Science	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	9ylxvPBp
11	region-group	\N	0	Unspecified	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	jXk0qPZN
12	region-group	\N	0	Cervical	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	EXP8GkB9
13	region-group	\N	0	Thoracal	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	d9kvelnK
14	region-group	\N	0	Lumbar	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	nBkE2P40
15	region-group	\N	0	Sacrococcygeal	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	dDM2dk8Y
16	region-group	\N	0	Shoulder Joint	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	oYMybldJ
17	region-group	\N	0	Arm	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	bVleZk71
18	region-group	\N	0	Elbow Joint	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	X5ldAkQy
19	region-group	\N	0	Forearm	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	mrkDvMV0
20	region-group	\N	0	Wrist Joint	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	WalRVlRg
21	region-group	\N	0	Hand	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	9RkVZk6V
22	region-group	\N	0	Pelvic	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	8ZMW4l91
23	region-group	\N	0	Hip Joint	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	74lOeP9e
24	region-group	\N	0	Thigh	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	9Wlrnl5E
25	region-group	\N	0	Knee Joint	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	YWPwBkAg
26	region-group	\N	0	Lower Leg	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	O7kqyk92
27	region-group	\N	0	Ankle Joint	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	VBPAeP73
28	region-group	\N	0	Foot	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	oRP4XPaz
29	region-group	\N	0	No Region	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	4RlQdMzJ
30	region-group	\N	0	Multiple Region	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	NKMo6Pa8
31	specific-part	\N	0	Unspecified	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	8Ql74k6g
32	specific-part	\N	0	Pathogenesis	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	VmljJlJW
33	specific-part	\N	0	Diagnosis/Investigation	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	1GPK4MJY
34	specific-part	\N	0	Treatment/Management	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	zyPN1kn1
35	specific-part	\N	0	Prognosis and Complication	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	L0MZ8kaQ
36	typical-group	\N	0	Unspecified	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	e9MYbl1o
37	typical-group	\N	0	Analysis	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	Kjknyle6
38	typical-group	\N	0	Recall Type	\N	2017-07-09 21:14:39	2017-07-09 21:14:39	3	v0PB3MoV
39	disease-group	\N	0	abc	\N	2017-12-06 20:32:38	2017-12-06 20:32:38	3	Anl3mPNm
40	region-group	\N	0	Thoracolumbal	\N	2020-11-15 20:06:40	2020-11-15 20:07:03	3	oYlbOkNe
41	disease-group	\N	0	Ethic	\N	2020-11-16 21:12:41	2020-11-16 21:12:42	3	J9l1JkYd
\.


--
-- Data for Name: category_item; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.category_item (category_id, item_id) FROM stdin;
\.


--
-- Data for Name: category_question; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.category_question (category_id, question_id) FROM stdin;
\.


--
-- Data for Name: clients; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.clients (id, name, slug, domains, is_active, settings, primary_contact_email, primary_contact_phone, notes, created_at, updated_at, deleted_at, logo) FROM stdin;
1	ACME Corporation	acme-corp	["acme.localhost","acme.example.com"]	t	{"theme":"blue","max_attempts":3,"time_zone":"America\\/New_York"}	admin@acme.com	+1234567890	Premium client with enterprise features	2025-11-04 12:00:50	2025-11-04 12:00:50	\N	\N
2	Tech Education Institute	tech-edu	["techedu.localhost","techedu.example.com"]	t	{"theme":"green","max_attempts":5,"time_zone":"Europe\\/London"}	admin@techedu.com	+0987654321	Educational institution with special pricing	2025-11-04 12:00:50	2025-11-04 12:00:50	\N	\N
3	National Orthopaedic and Traumatology Board Examination	ionbec	["ionbec.com","www.ionbec.com"]	t	{"theme":"default","max_attempts":3,"time_zone":"Asia\\/Jakarta"}	admin@ionbec.com	+1234567890	Main Ionbec platform client	2025-11-04 12:07:13	2025-11-04 12:07:13	\N	\N
\.


--
-- Data for Name: deliveries; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.deliveries (id, exam_id, group_id, name, scheduled_at, duration, ended_at, is_anytime, automatic_start, is_finished, last_status, display_name, created_at, updated_at, client_id, hash) FROM stdin;
72	22	51	NATIONAL BOARD EXAM JAKARTA 191218 - OSCE	2018-12-19 08:00:00	150	\N	f	f	\N	\N	NATIONAL BOARD EXAM JAKARTA 191218 - OSCE	2018-12-18 01:19:37	2018-12-18 01:24:50	3	2bb848f78a134db958ebe8b6b503312f
73	22	50	NATIONAL BOARD EXAM SURABAYA 191218 - OSCE	2018-12-19 08:00:00	150	\N	f	f	\N	\N	NATIONAL BOARD EXAM SURABAYA 191218 - OSCE	2018-12-18 01:21:01	2018-12-18 01:24:36	3	dc93a24bc89e96acc9c4b7969757f8f0
74	23	51	NATIONAL BOARD EXAM JAKARTA 191218 - MCQ	2018-12-19 11:00:00	120	\N	f	f	\N	\N	NATIONAL BOARD EXAM JAKARTA 191218 - MCQ	2018-12-18 01:23:37	2018-12-18 01:23:37	3	f0340bbb3f32a7a6508b7f3a85713fbe
75	23	50	NATIONAL BOARD EXAM SURABAYA 191218 - MCQ	2018-12-19 11:00:00	120	\N	f	f	\N	\N	NATIONAL BOARD EXAM SURABAYA 191218 - MCQ	2018-12-18 01:24:22	2018-12-18 01:24:22	3	4572f66cfdd5893ed837e4a6902fb530
76	9	52	TRY OUT CBT 21-05-2019 (OSCE)	2019-05-21 08:00:00	150	\N	f	f	\N	\N	TRY OUT CBT 21-05-2019 (OSCE)	2019-05-21 05:56:40	2019-05-21 05:56:40	3	60444d43e965514fc49d890cbbab8964
77	8	52	TRY OUT CBT 21-05-2019 (MCQ)	2019-05-21 11:00:00	120	\N	f	f	\N	\N	TRY OUT CBT 21-05-2019 (MCQ)	2019-05-21 06:17:39	2019-05-21 06:17:39	3	0d57b5e3c744d90a600d1a8d9d01ba69
78	27	53	National Board Examination 29-05-2019 Surabaya (OSCE)	2019-05-29 08:00:00	150	\N	f	f	\N	\N	National Board Examination 29-05-2019 Surabaya (OSCE)	2019-05-29 00:02:40	2019-05-29 00:17:02	3	9648d06cb1272f0a7e9c6aadcc0dea4b
79	26	53	National Board Examination 29-05-2019 Surabaya (MCQ)	2019-05-29 11:00:00	120	\N	f	f	\N	\N	National Board Examination 29-05-2019 Surabaya (MCQ)	2019-05-29 00:10:20	2019-05-29 00:11:05	3	56a5d0f9c38de742a87e267eec765e8f
80	27	55	National Board Examination 29-05-2019 Jakarta (OSCE)	2019-05-29 08:00:00	150	\N	f	f	\N	\N	National Board Examination 29-05-2019 Jakarta (OSCE)	2019-05-29 00:17:58	2019-05-29 00:17:58	3	67f0fbe0aa3f4223959121df170b3c31
81	26	55	National Board Examination 29-05-2019 Jakarta (MCQ)	2019-05-29 11:00:00	120	\N	f	f	\N	\N	National Board Examination 29-05-2019 Jakarta (MCQ)	2019-05-29 00:24:02	2019-05-29 00:24:02	3	f5793c4af9126a92bb8dba754ef7fd7f
83	9	56	TRIAL CBT UI 5/11/19 SHORT ESSAY	2019-11-05 08:00:00	150	\N	f	f	\N	\N	TRIAL CBT UI 5/11/19 SHORT ESSAY	2019-11-05 07:07:22	2019-11-05 07:07:22	3	55e4933b1dd3c2f3a56d700ebdee6687
84	9	58	TRIAL CBT UNAIR 5/11/19 SHORT ESSAY	2019-11-05 08:00:00	150	\N	f	f	\N	\N	TRIAL CBT UNAIR 5/11/19 SHORT ESSAY	2019-11-05 07:11:03	2019-11-05 07:11:03	3	7d63b7f5422b0e6a795c208b7e856873
85	9	57	TRIAL CBT 5/11/19 UNPAD SHORT ESSAY	2019-11-05 08:00:00	150	\N	f	f	\N	\N	TRIAL CBT 5/11/19 UNPAD SHORT ESSAY	2019-11-05 07:12:51	2019-11-05 07:12:51	3	b52d847c9a7de3c055df2d345dcbfa01
86	9	59	TRIAL CBT UNHAS 5/11/19 SHORT ESSAY	2019-11-05 08:30:00	150	\N	f	f	\N	\N	TRIAL CBT UNHAS 5/11/19 SHORT ESSAY	2019-11-05 07:14:45	2019-11-05 08:22:14	3	4eb6cc5c931f3d7a7d0e362e26a09182
87	9	60	TRIAL CBT UNS 5/11/19 SHORT ESSAY	2019-11-05 08:00:00	150	\N	f	f	\N	\N	TRIAL CBT UNS 5/11/19 SHORT ESSAY	2019-11-05 07:15:54	2019-11-05 07:15:54	3	a4ffc10e3b3a8ae0d10e166fd566f301
88	9	61	TRIAL CBT UGM 5/11/19 SHORT ESSAY	2019-11-05 08:00:00	150	\N	f	f	\N	\N	TRIAL CBT UGM 5/11/19 SHORT ESSAY	2019-11-05 07:16:59	2019-11-05 07:16:59	3	6c87de0dc751957ab805f52cb748e903
89	9	62	TRIAL CBT UBUD 5/11/19 SHORT ESSAY	2019-11-05 08:00:00	150	\N	f	f	\N	\N	TRIAL CBT UBUD 5/11/19 SHORT ESSAY	2019-11-05 07:18:18	2019-11-05 07:18:18	3	8350fa6c66b9f18ac48d1dc440cd31eb
90	9	63	TRIAL CBT UB 5/11/19 SHORT ESSAY	2019-11-05 08:00:00	150	\N	f	f	\N	\N	TRIAL CBT UB 5/11/19 SHORT ESSAY	2019-11-05 07:19:35	2019-11-05 07:19:35	3	f5f6983d9fd202b9a97bd154fdf30dae
91	9	64	TRIAL CBT USU 5/11/19 SHORT ESSAY	2019-11-05 08:00:00	150	\N	f	f	\N	\N	TRIAL CBT USU 5/11/19 SHORT ESSAY	2019-11-05 07:20:35	2019-11-05 07:20:35	3	ea3de35de4668086b2881f943a686adc
92	9	65	Trial cbt UB 5/11/19 short essay susulan	2019-11-05 08:12:00	150	\N	f	f	\N	\N	Trial cbt UB 5/11/19 short essay susulan	2019-11-05 08:03:51	2019-11-05 08:03:51	3	0ee11bfc5a48797a45496ba8c0599c45
93	15	56	TRIAL CBT UI 5/11/19 MCQ	2019-11-05 11:00:00	120	\N	f	f	\N	\N	TRIAL CBT UI 5/11/19 MCQ	2019-11-05 09:25:37	2019-11-05 09:25:37	3	05e2245ecc49c3999736b3981d65b098
94	15	58	TRIAL CBT UNAIR 5/11/19 MCQ	2019-11-05 11:00:00	120	\N	f	f	\N	\N	TRIAL CBT UNAIR 5/11/19 MCQ	2019-11-05 09:27:21	2019-11-05 09:27:21	3	f80dace61c79be59426025fd64fe7acf
95	15	57	TRIAL CBT UNPAD 5/11/19 MCQ	2019-11-05 11:00:00	120	\N	f	f	\N	\N	TRIAL CBT UNPAD 5/11/19 MCQ	2019-11-05 09:28:45	2019-11-05 09:28:45	3	1666d0458106f290e475a742e4122611
96	15	59	TRIAL CBT UNHAS 5/11/19 MCQ	2019-11-05 11:00:00	120	\N	f	f	\N	\N	TRIAL CBT UNHAS 5/11/19 MCQ	2019-11-05 09:32:50	2019-11-05 10:54:53	3	317e29a13f4d5a960153c484027feb4e
98	15	60	TRIAL CBT UNS 5/11/19 MCQ	2019-11-05 11:00:00	180	\N	f	f	\N	\N	TRIAL CBT UNS 5/11/19 MCQ	2019-11-05 09:35:36	2019-11-05 12:34:52	3	4ba394ce8ae15b43d291e1a30b8715b9
99	15	63	TRIAL CBT UB 5/11/19 MCQ	2019-11-05 11:00:00	120	\N	f	f	\N	\N	TRIAL CBT UB 5/11/19 MCQ	2019-11-05 09:37:12	2019-11-05 09:37:12	3	5de79969e351f5ffaff80a473f5ac168
100	15	64	TRIAL CBT USU 5/11/19 MCQ	2019-11-05 11:00:00	120	\N	f	f	\N	\N	TRIAL CBT USU 5/11/19 MCQ	2019-11-05 09:38:19	2019-11-05 09:38:19	3	3e0198597b91407810fa0d8d279f5ced
101	15	62	TRIAL CBT UNUD 5/11/19 MCQ	2019-11-05 11:00:00	120	\N	f	f	\N	\N	TRIAL CBT UNUD 5/11/19 MCQ	2019-11-05 09:39:40	2019-11-05 09:39:40	3	09982521c3a4199f89e790aadbcf782d
102	15	65	TRIAL CBT UB 5/11/19 MCQ SUSULAN	2019-11-05 11:20:00	120	\N	f	f	\N	\N	TRIAL CBT UB 5/11/19 MCQ SUSULAN	2019-11-05 09:42:00	2019-11-05 09:42:00	3	08dd188191c970efc2bef84cd0046d16
103	28	66	NATIONAL BOARD EXAMINATION  SURABAYA 13 NOVEMBER 2019	2019-11-13 08:00:00	150	\N	f	f	\N	\N	NATIONAL BOARD EXAMINATION  SURABAYA 13 NOVEMBER 2019	2019-11-13 05:01:36	2019-11-13 05:01:36	3	fe4c575053157b574c22d750ed57f1c5
104	28	67	NATIONAL BOARD EXAMINATION  JAKARTA 13 NOVEMBER 2019	2019-11-13 08:00:00	150	\N	f	f	\N	\N	NATIONAL BOARD EXAMINATION  JAKARTA 13 NOVEMBER 2019	2019-11-13 05:07:09	2019-11-13 05:07:09	3	d1898b135995d65dc5c83aab0d8fb509
105	29	66	NATIONAL BOARD EXAMINATION SURABAYA 13 NOVEMBER 2019 - MCQ	2019-11-13 11:00:00	120	\N	f	f	\N	\N	NATIONAL BOARD EXAMINATION SURABAYA 13 NOVEMBER 2019 - MCQ	2019-11-13 05:12:22	2019-11-13 05:12:22	3	ff01baaa9b4266c00b83eb4b7a2fa624
106	29	67	NATIONAL BOARD EXAMINATION JAKARTA 13 NOVEMBER 2019 - MCQ	2019-11-13 11:00:00	120	\N	f	f	\N	\N	NATIONAL BOARD EXAMINATION JAKARTA 13 NOVEMBER 2019 - MCQ	2019-11-13 05:16:14	2019-11-13 05:16:14	3	22ac6023bb86b9e29e710414acdd8d68
107	30	68	NATIONALBOARD EXAMINATION IHKS FELLOWSHIP # April 2020	2020-04-03 13:30:00	100	\N	f	f	\N	\N	NATIONALBOARD EXAMINATION IHKS FELLOWSHIP # April 2020	2020-04-02 22:14:07	2020-04-02 22:14:07	3	50c85756ad8a1cb95cc4a7aa2fc01772
108	13	68	trial ihks	2020-04-02 22:31:00	100	\N	t	f	\N	\N	trial ihks	2020-04-02 22:32:19	2020-04-02 22:32:19	3	0b57667d64792f626009f50d2bb9116b
109	9	69	TRY OUT CBT WIB-OSCE-5520	2020-05-05 08:00:00	125	\N	f	f	\N	\N	TRY OUT CBT WIB-OSCE-5520	2020-05-04 23:39:11	2020-05-05 06:30:59	3	cf8258ef264362c38614f47edf9eb89c
110	8	69	TRY OUT CBT WIB-MCQ-5520	2020-05-05 10:15:00	100	\N	f	f	\N	\N	TRY OUT CBT WIB-MCQ-5520	2020-05-04 23:41:04	2020-05-05 05:52:46	3	8ac48a4a3ae408b51cf6e3480d7ff428
111	9	70	TRY OUT CBT WITA-OSCE-5520	2020-05-05 08:00:00	125	\N	f	f	\N	\N	TRY OUT CBT WITA-OSCE-5520	2020-05-04 23:44:04	2020-05-05 06:30:38	3	aeffd5b0cc7e1abee1845e890b6ef352
154	43	2	mcq test	2025-11-05 04:32:00	30	2025-11-05 05:02:00	f	t	\N	Created	mcq test	2025-11-04 21:32:36	2025-11-04 21:32:36	3	Wxrj1oG6
53	15	39	NATIONAL BOARD EXAM MCQ 18 JULI 2018 JAKARTA	2018-07-18 10:55:00	120	\N	f	f	\N	\N	NATIONAL BOARD EXAM MCQ 18 JULI 2018 JAKARTA	2018-07-12 21:50:45	2018-07-18 10:49:49	3	688a769757f8ed6ad26e0d421839b3ee
54	15	40	NATIONAL BOARD EXAM MCQ 18 JULI 2018 SURABAYA	2018-07-18 10:55:00	120	\N	f	f	\N	\N	NATIONAL BOARD EXAM MCQ 18 JULI 2018 SURABAYA	2018-07-12 21:53:47	2018-07-18 10:49:32	3	2016cfddc19acf7c38559f2490482d37
57	17	39	NATIONAL BOARD EXAM ESSAY 18 JULI 2018 JAKARTA	2018-07-18 08:05:00	150	\N	f	f	\N	\N	NATIONAL BOARD EXAM ESSAY 18 JULI 2018 JAKARTA	2018-07-15 10:59:23	2018-07-16 20:47:05	3	cea4c69ca60e76f50528e8a214755511
58	17	40	NATIONAL BOARD EXAM ESSAY 18 JULI 2018 SURABAYA	2018-07-18 08:05:00	150	\N	f	f	\N	\N	NATIONAL BOARD EXAM ESSAY 18 JULI 2018 SURABAYA	2018-07-15 11:00:05	2018-07-16 20:46:57	3	cdc1ee6610f3da7b37e315b5f9b73efb
61	21	44	21-11-2018	2018-11-21 09:00:00	100	\N	f	f	\N	\N	21-11-2018	2018-11-20 06:32:20	2018-11-20 06:32:20	3	ef8ca1e3c4f140d4e70a1fc78595b68b
62	20	44	21-11-2018 - MCQ	2018-11-21 10:50:00	90	\N	f	f	\N	\N	21-11-2018 - MCQ	2018-11-20 07:01:47	2018-11-21 09:07:33	3	06de5adf601c196c16980efc37600c52
63	20	45	trisl 20-11-2018	2018-11-20 07:03:00	90	\N	t	f	\N	\N	trisl 20-11-2018	2018-11-20 07:05:35	2018-11-20 07:05:35	3	a3f3ae242dbaeaa71a01ceae8750eb21
64	21	45	20-11-2018 Essay	2018-11-20 07:08:00	100	\N	t	f	\N	\N	20-11-2018 Essay	2018-11-20 07:08:45	2018-11-20 07:08:45	3	005dedd342086e9fa1905a4fc6badc2c
65	17	44	21-11-2018 - OSCE	2018-11-21 09:00:00	100	\N	f	f	\N	\N	21-11-2018 - OSCE	2018-11-21 06:32:51	2018-11-21 06:32:51	3	eb2054e49f9633c7c887442e04972072
66	21	44	23-11-2018	2018-11-23 13:00:00	100	\N	f	f	\N	\N	23-11-2018	2018-11-23 06:46:47	2018-11-23 07:57:15	3	1aff632e2768525dc542b738a14f64af
67	20	44	23-11-2018 MCQ	2018-11-23 14:50:00	90	\N	f	f	\N	\N	23-11-2018 MCQ	2018-11-23 06:47:39	2018-11-23 07:57:33	3	55f819d45bb4b00eb6547737ed96a036
112	8	70	TRY OUT CBT WITA-MCQ-5520	2020-05-05 10:15:00	100	\N	f	f	\N	\N	TRY OUT CBT WITA-MCQ-5520	2020-05-04 23:45:11	2020-05-05 00:11:31	3	59edb6151f854c41d31eb0320a7ada75
123	13	76	yoppi coba	2020-12-15 18:50:00	30	\N	t	f	\N	\N	yoppi coba	2020-12-15 18:51:16	2020-12-15 18:51:16	3	d1304a5797fba2f35f67b16d92bd753f
124	31	77	TRY OUT CBT 050521 - OSCE	2021-05-05 08:00:00	25	\N	f	f	\N	\N	TRY OUT CBT 050521 - OSCE	2021-05-05 05:20:37	2021-05-05 05:28:34	3	3cb33444cf40a7476d7a2d70be7d5038
125	32	77	TRY OUT CBT 050521 - MCQ	2021-05-05 08:30:00	25	\N	f	f	\N	\N	TRY OUT CBT 050521 - MCQ	2021-05-05 05:31:21	2021-05-05 08:39:22	3	d655e1b11dfebae1e341b40dcbfe5003
127	38	79	NATIONAL BOARD EXAMINATION - ORTHOPAEDIC AND TRAUMATOLOGY - OSCE - 27-05-2021	2021-05-27 08:00:00	150	\N	f	f	\N	\N	NATIONAL BOARD EXAMINATION - ORTHOPAEDIC AND TRAUMATOLOGY - OSCE - 27-05-2021	2021-05-26 22:55:53	2021-05-26 22:55:53	3	41411942c71209d8c56a35cd260fbd9b
128	37	79	NATIONAL BOARD EXAMINATION - ORTHOPAEDIC AND TRAUMATOLOGY - mcq - 27-05-2021	2021-05-27 11:00:00	120	\N	f	f	\N	\N	NATIONAL BOARD EXAMINATION - ORTHOPAEDIC AND TRAUMATOLOGY - mcq - 27-05-2021	2021-05-26 22:56:52	2021-05-26 22:56:52	3	f67e63ab5830a7be7435064bb88253ee
142	42	86	NATIONAL BOARD EXAMINATION - CBT - OSCE - 24-05-2022	2022-05-24 08:00:00	150	\N	f	f	\N	Overdue	NATIONAL BOARD EXAMINATION - CBT - OSCE - 24-05-2022	2022-05-24 05:52:59	2025-11-04 12:41:57	3	256a5c15226cd2234094d5207f478a6a
141	40	85	TRY OUT 180522 - MCQ	2022-05-18 07:50:00	10	\N	f	f	\N	Overdue	TRY OUT 180522 - MCQ	2022-05-17 16:46:41	2025-11-04 12:41:57	3	9b20a8cc8d921f850f205274d490dfb4
140	39	85	TRY OUT 180522 - OSCE	2022-05-18 07:30:00	12	\N	f	f	\N	Overdue	TRY OUT 180522 - OSCE	2022-05-17 15:57:39	2025-11-04 12:41:57	3	07f51b05c374e6a020f65cd90934289e
138	29	84	ADAPTATION EXAMINATION - ORTHOPAEDIC AND TRAUMATOLOGY - MCQ 040422	2022-04-05 09:50:00	120	\N	f	f	\N	Overdue	ADAPTATION EXAMINATION - ORTHOPAEDIC AND TRAUMATOLOGY - MCQ 040422	2022-04-03 12:24:52	2025-11-04 12:41:57	3	38e242454e2ee1fb51f87d0c8f43757a
139	28	84	ADAPTATION EXAMINATION - ORTHOPAEDIC AND TRAUMATOLOGY - OSCE 040422	2022-04-05 07:00:00	150	\N	f	f	\N	Overdue	ADAPTATION EXAMINATION - ORTHOPAEDIC AND TRAUMATOLOGY - OSCE 040422	2022-04-03 12:34:57	2025-11-04 12:41:57	3	9597e83b532621f5dae6f72c497f30e6
134	36	82	ADAPTATION EXAMINATION - ORTHOPAEDIC AND TRAUMATOLOGY - MCQ - 29 OKT 2021	2021-10-29 09:45:00	120	\N	f	f	\N	Overdue	ADAPTATION EXAMINATION - ORTHOPAEDIC AND TRAUMATOLOGY - MCQ - 29 OKT 2021	2021-10-26 22:15:30	2025-11-04 12:41:57	3	b94f611d755abc93a84af373510ecc12
133	35	82	ADAPTATION EXAMINATION - ORTHOPAEDIC AND TRAUMATOLOGY - OSCE - 29 OKT 2021	2021-10-29 07:00:00	150	\N	f	f	\N	Overdue	ADAPTATION EXAMINATION - ORTHOPAEDIC AND TRAUMATOLOGY - OSCE - 29 OKT 2021	2021-10-26 22:13:52	2025-11-04 12:41:57	3	86d12bb20d47d78f1be9f305e285c0b9
137	13	81	Trial CBT Adaptasi Sp.OT- MCQ	2021-10-28 13:10:00	60	\N	f	f	\N	Overdue	Trial CBT Adaptasi Sp.OT- MCQ	2021-10-27 14:07:00	2025-11-04 12:41:57	3	9dbcea87d97dc50d974d7baf56d16449
135	31	81	Trial CBT Adaptasi Sp.OT	2021-10-28 12:30:00	30	\N	f	f	\N	Overdue	Trial CBT Adaptasi Sp.OT	2021-10-27 13:58:24	2025-11-04 12:41:57	3	a618d11870959f0415d3c8944521e4a7
136	31	81	Trial CBT Adaptasi Sp.OT	2021-10-28 12:30:00	30	\N	f	f	\N	Overdue	Trial CBT Adaptasi Sp.OT	2021-10-27 13:58:24	2025-11-04 12:41:57	3	58d5d266ba038344b24e8e08ab06c2ea
129	30	80	CBT Exam Fellowship IHKS Hospital-based Soetomo July 2021	2021-07-02 13:00:00	100	\N	f	f	\N	Overdue	CBT Exam Fellowship IHKS Hospital-based Soetomo July 2021	2021-07-01 07:11:51	2025-11-04 12:41:57	3	d9d10fcf675984878bc493eb6e9e7b9b
132	12	80	Try out IHKS Juli 2021	2021-07-01 10:00:00	30	\N	f	f	\N	Overdue	Try out IHKS Juli 2021	2021-07-01 07:29:11	2025-11-04 12:41:57	3	17d9d0ae4d5515280fa83194d33f3042
68	9	52	TRY OUT CBT 21-05-2019 (OSCE)	2019-05-21 08:00:00	150	\N	f	f	\N	\N	TRY OUT CBT 21-05-2019 (OSCE)	2018-12-10 22:58:17	2019-05-21 05:40:09	3	611ae42dd87a73905d5ca6f701ac0c54
69	8	46	TRIAL CBT 11-12-2018 (MCQ)	2018-12-11 11:10:00	100	\N	f	f	\N	\N	TRIAL CBT 11-12-2018 (MCQ)	2018-12-10 23:23:48	2018-12-11 10:57:27	3	b36f932b2f749030538768a61f8dba82
70	9	47	TRIAL CBT 11-12-2018 SENDIRI	2018-12-11 09:00:00	100	\N	f	f	\N	\N	TRIAL CBT 11-12-2018 SENDIRI	2018-12-11 08:50:13	2018-12-11 08:51:36	3	583688281b9761beb64a158a6c519498
97	15	61	TRIAL CBT UGM 5/11/19 MCQ	2019-11-05 11:00:00	120	\N	f	f	\N	\N	TRIAL CBT UGM 5/11/19 MCQ	2019-11-05 09:33:58	2019-11-05 09:33:58	3	c87127e77d16482235291b23828ef768
150	29	87	ADAPTATION EXAMINATION TEST - MCQ - 120922	2022-09-12 11:05:00	120	\N	f	f	\N	Overdue	ADAPTATION EXAMINATION TEST - MCQ - 120922	2022-09-12 11:01:55	2025-11-04 12:41:57	3	ec01c0996944a826832a4af26859250c
149	42	87	ADAPTATION EXAMINATION - OSCE - 120922	2022-09-12 08:10:00	150	\N	f	f	\N	Overdue	ADAPTATION EXAMINATION - OSCE - 120922	2022-09-12 08:06:34	2025-11-04 12:41:57	3	76a2de4f4ecf4109b7919307480228d9
143	41	86	NATIONAL BOARD EXAMINATION - CBT - MCQ - 24-05-2022	2022-05-24 11:00:00	120	\N	f	f	\N	Overdue	NATIONAL BOARD EXAMINATION - CBT - MCQ - 24-05-2022	2022-05-24 06:07:19	2025-11-04 12:41:57	3	f5ad2ca96b1e5cc6c445381738b287cf
152	43	1	Test2025	2025-11-05 00:00:00	120	2025-11-05 02:00:00	f	t	\N	Scheduled	Test2025	2025-11-04 17:41:43	2025-11-04 17:41:57	3	26EAx9r9
71	8	47	TRIAL CBT 11-12-2018 SENDIRI	2018-12-11 10:55:00	100	\N	f	f	\N	Scoring	TRIAL CBT 11-12-2018 SENDIRI	2018-12-11 08:51:16	2025-11-04 19:09:20	3	02255eb25d6f0352e8d836fecdd10623
82	5	41	Demo Test	2019-10-01 18:12:00	90	\N	t	f	\N	\N	Demo Test	2019-10-01 18:13:00	2019-10-01 18:13:00	3	fc9a34482b0d0713cf506bc3de5ef1da
153	44	2	osce test	2025-11-05 04:30:00	15	2025-11-05 04:45:00	f	t	\N	Created	osce test	2025-11-04 21:31:10	2025-11-04 21:31:10	3	yMrOv8GJ
113	32	71	Try Out MCQ - WIB - 080520	2020-05-08 09:00:00	50	\N	f	f	\N	\N	Try Out MCQ - WIB - 080520	2020-05-07 21:02:29	2020-05-07 21:02:29	3	9d312f6369eebe0bb2ac07c964d69e0c
114	32	72	Try Out  MCQ - WITA - 080520	2020-05-08 09:00:00	50	\N	f	f	\N	\N	Try Out  MCQ - WITA - 080520	2020-05-07 21:04:21	2020-05-07 21:04:21	3	e03a87c38c44027f84633fd260cdf204
115	31	71	Try Out OSCE - WIB - 080520	2020-05-08 08:00:00	30	\N	f	f	\N	\N	Try Out OSCE - WIB - 080520	2020-05-07 21:13:37	2020-05-07 21:13:37	3	e0e90b3e813229622a0abed28fb7d874
116	31	72	Try Out OSCE - WITA - 080520	2020-05-08 08:00:00	30	\N	f	f	\N	\N	Try Out OSCE - WITA - 080520	2020-05-07 21:14:34	2020-05-07 21:14:34	3	4da14ce927c976891eb359d08ca7fc94
117	30	73	National Board Examination of IHKS Fellowship Programe	2020-10-16 13:00:00	150	\N	f	f	\N	\N	National Board Examination of IHKS Fellowship Programe	2020-10-16 00:45:11	2020-10-16 14:01:41	3	fd067de7c54a90ced462ea80224b3b8e
118	31	74	TRY OUT OSCE CBT 101120	2020-11-10 08:30:00	30	\N	f	f	\N	\N	TRY OUT OSCE CBT 101120	2020-11-10 05:39:21	2020-11-10 05:39:21	3	cdced876d74a833358ec8cfcb4281a76
119	32	74	TRY OUT MCQ CBT 101120	2020-11-10 09:55:00	18	\N	f	f	\N	\N	TRY OUT MCQ CBT 101120	2020-11-10 05:48:40	2020-11-10 09:47:26	3	9b3bb96b894525d13cba8043766e85a4
120	35	75	NATIONAL BOARD EXAMINATION - OSCE - 18 NOV 2020	2020-11-18 08:00:00	150	\N	f	f	\N	\N	NATIONAL BOARD EXAMINATION - OSCE - 18 NOV 2020	2020-11-17 21:12:38	2020-11-17 21:12:38	3	ad6e30dee11e04635719759b63662371
121	36	75	NATIONAL BOARD EXAMINATION - MCQ - 18 NOV 2020	2020-11-18 11:00:00	120	\N	f	f	\N	\N	NATIONAL BOARD EXAMINATION - MCQ - 18 NOV 2020	2020-11-17 21:18:05	2020-11-17 21:18:05	3	fde34140a3229422f791e4b1d36a9e6a
122	7	76	test	2020-12-15 18:48:00	30	\N	t	f	\N	\N	test	2020-12-15 18:49:15	2020-12-15 18:49:15	3	566dbdc65899070809c101bd95752deb
\.


--
-- Data for Name: delivery_snapshots; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.delivery_snapshots (id, delivery_id, exam_id, exam_structure, total_questions, total_items, created_at, updated_at) FROM stdin;
2	152	43	{"items": [{"id": 1, "type": {"name": "Multiple Choice", "value": "multiple-choice", "preference": {"is_visible": true, "description": null, "has_answers": true, "is_scoreable": true}}, "order": 1, "title": "Test 01-0411", "content": null, "is_random": true, "questions": [{"id": 1, "type": {"name": "multiple-choice", "label": "Multiple Choice", "is_visible": true, "description": null, "has_answers": true, "is_scoreable": true}, "order": 1, "score": 100, "answers": [{"id": 1, "answer": "<p>Emergent surgery, including open carpal tunnel release, open reduction of the perilunate dislocation, repair of the scapholunate ligament, and intercarpal pinning</p>", "question_id": 1, "is_correct_answer": true}, {"id": 2, "answer": "<p>Emergent surgery, including open carpal tunnel release, closed reduction of the perilunate dislocation, and casting</p>", "question_id": 1, "is_correct_answer": false}, {"id": 3, "answer": "<p>Elective outpatient surgery, including open carpal tunnel release, open reduction of the perilunate dislocation, repair of the scapholunate ligament, and intercarpal pinning</p>", "question_id": 1, "is_correct_answer": false}, {"id": 4, "answer": "<p>Emergent surgery, including open reduction of the perilunate dislocation, repair of the scapholunate ligament, and intercarpal pinning</p>", "question_id": 1, "is_correct_answer": false}, {"id": 5, "answer": "<p>Emergent surgery, including open reduction of the perilunate dislocation, repair of the scapholunate ligament, and intercarpal pinning</p>", "question_id": 1, "is_correct_answer": false}], "item_id": 1, "question": "<p style=\\"text-align: justify\\">Figures 1 and 2 show the postreduction radiographs obtained from a 32-year-old man who fell from a ladder onto his outstretched right arm. He reports right wrist pain and dense numbness in his radial digits. What is the most appropriate treatment option?&nbsp;</p>", "is_random": true}], "attachments": [{"id": "e638f3b9-006a-4021-8c26-425fe3da7bc8", "mime": "png", "path": "attachments/VxgOjk3W-jOqoGMG7xD.png"}, {"id": "48eb006d-20c2-4d4a-81b5-374ebf5f7c27", "mime": "png", "path": "attachments/VxgOjk3W-mgUjQvu73w.png"}], "is_vignette": false}, {"id": 2, "type": {"name": "Multiple Choice", "value": "multiple-choice", "preference": {"is_visible": true, "description": null, "has_answers": true, "is_scoreable": true}}, "order": 2, "title": "Test 02-0411", "content": null, "is_random": true, "questions": [{"id": 3, "type": {"name": "multiple-choice", "label": "Multiple Choice", "is_visible": true, "description": null, "has_answers": true, "is_scoreable": true}, "order": 1, "score": 100, "answers": [{"id": 11, "answer": "<p>Osteosarcoma</p>", "question_id": 3, "is_correct_answer": false}, {"id": 12, "answer": "<p>Enchondroma</p>", "question_id": 3, "is_correct_answer": false}, {"id": 13, "answer": "<p>Fibrous dysplasia</p>", "question_id": 3, "is_correct_answer": false}, {"id": 14, "answer": "<p>Chondrosarcoma</p>", "question_id": 3, "is_correct_answer": true}, {"id": 15, "answer": "<p>Chondromyxoid fibroma</p>", "question_id": 3, "is_correct_answer": false}], "item_id": 2, "question": "<p>A 51-year-old woman has shoulder pain after a minor fall. A radiograph, MRI scan, are seen in Figures 1 through 2. Biopsy specimens are seen in Figures 3. What is the most likely diagnosis?</p>", "is_random": true}], "attachments": [{"id": "520f849f-42d5-4df2-8ee6-b85299a718ee", "mime": "png", "path": "attachments/qzKoYkDo-GITJLRVB6F.png"}, {"id": "8512c878-0158-40a0-852f-f2ca436963fa", "mime": "png", "path": "attachments/qzKoYkDo-GhSzNWtCsa.png"}, {"id": "f7b7e186-56d0-4ea8-a420-56d9b16244a9", "mime": "png", "path": "attachments/qzKoYkDo-zUbJ4hiRy7.png"}], "is_vignette": false}, {"id": 3, "type": {"name": "Multiple Choice", "value": "multiple-choice", "preference": {"is_visible": true, "description": null, "has_answers": true, "is_scoreable": true}}, "order": 3, "title": "Test 03-0411", "content": null, "is_random": true, "questions": [{"id": 4, "type": {"name": "multiple-choice", "label": "Multiple Choice", "is_visible": true, "description": null, "has_answers": true, "is_scoreable": true}, "order": 1, "score": 100, "answers": [{"id": 16, "answer": "<p>MRI and biopsy from an orthopedic oncologist</p>", "question_id": 4, "is_correct_answer": false}, {"id": 17, "answer": "<p>Marginal resection with complete removal of cartilage cap</p>", "question_id": 4, "is_correct_answer": true}, {"id": 18, "answer": "<p>Educate the family about the benign nature of the condition and prescribe NSAIDs until the pain diminishes</p>", "question_id": 4, "is_correct_answer": false}, {"id": 19, "answer": "<p>Referral to genetics for screening for potential associated malignancies</p>", "question_id": 4, "is_correct_answer": false}, {"id": 20, "answer": "<p>Intralesional curettage and adjuvant therapy</p>", "question_id": 4, "is_correct_answer": false}], "item_id": 3, "question": "<p>A skeletally-mature 14-year-old girl presents with her parents to your clinic with a \\"lump\\" near her knee. She is very bothered by the appearance of her knee and it is very painful when she bumps the palpable prominence. An AP x-ray is shown in figures 1, respectively. She has no other similar lesions elsewhere in her body, and her parents are unaware of any relevant family history. What is the next best step in management?&nbsp;</p>", "is_random": true}], "attachments": [{"id": "5f74bb4e-0aeb-478a-80a7-178f3522907a", "mime": "png", "path": "attachments/YRgyGB68-wmY9h91aWl.png"}], "is_vignette": false}, {"id": 723, "type": {"name": "Multiple Choice", "value": "multiple-choice", "preference": {"is_visible": true, "description": null, "has_answers": true, "is_scoreable": true}}, "order": 4, "title": "Test 04-0411", "content": null, "is_random": true, "questions": [{"id": 1055, "type": {"name": "multiple-choice", "label": "Multiple Choice", "is_visible": true, "description": null, "has_answers": true, "is_scoreable": true}, "order": 1, "score": 100, "answers": [{"id": 26, "answer": "<p style=\\"text-align: justify\\">A positive Lhermitte sign.</p>", "question_id": 1055, "is_correct_answer": true}, {"id": 27, "answer": "<p style=\\"text-align: justify\\">A positive Spurling sign.</p>", "question_id": 1055, "is_correct_answer": false}, {"id": 28, "answer": "<p style=\\"text-align: justify\\">A positive Jackson sign.</p>", "question_id": 1055, "is_correct_answer": false}, {"id": 29, "answer": "<p style=\\"text-align: justify\\">A positive Lasegue sign.</p>", "question_id": 1055, "is_correct_answer": false}, {"id": 30, "answer": "<p style=\\"text-align: justify\\">A positive Hoffmann sign.</p>", "question_id": 1055, "is_correct_answer": false}], "item_id": 723, "question": "<p style=\\"text-align: justify\\">A 63-year-old man has a feeling of generalized clumsiness in his arms and hands, difficulty buttoning his shirt, and gradually worsening gait instability. During examination, his neck is gently passively flexed to end range while he is seated. The patient describes an electric shock-like sensation that radiates down the spine and into the extremities. This describes which of the following?&nbsp;</p>", "is_random": true}], "attachments": [], "is_vignette": false}], "is_mcq": true, "exam_id": 43, "metadata": {"total_items": 4, "total_questions": 4, "snapshot_created_at": "2025-11-04T17:41:44+00:00"}, "exam_code": "TEST25", "exam_name": "TEST25", "is_random": false, "is_interview": false}	4	4	2025-11-04 17:41:44	2025-11-04 17:41:44
3	153	44	{"items": [], "is_mcq": false, "exam_id": 44, "metadata": {"total_items": 0, "total_questions": 0, "snapshot_created_at": "2025-11-04T21:31:10+00:00"}, "exam_code": "osce test", "exam_name": "osce test", "is_random": true, "is_interview": false}	0	0	2025-11-04 21:31:10	2025-11-04 21:31:10
4	154	43	{"items": [{"id": 1, "type": {"name": "Multiple Choice", "value": "multiple-choice", "preference": {"is_visible": true, "description": null, "has_answers": true, "is_scoreable": true}}, "order": 1, "title": "Test 01-0411", "content": null, "is_random": true, "questions": [{"id": 1, "type": {"name": "multiple-choice", "label": "Multiple Choice", "is_visible": true, "description": null, "has_answers": true, "is_scoreable": true}, "order": 1, "score": 100, "answers": [{"id": 1, "answer": "<p>Emergent surgery, including open carpal tunnel release, open reduction of the perilunate dislocation, repair of the scapholunate ligament, and intercarpal pinning</p>", "question_id": 1, "is_correct_answer": true}, {"id": 2, "answer": "<p>Emergent surgery, including open carpal tunnel release, closed reduction of the perilunate dislocation, and casting</p>", "question_id": 1, "is_correct_answer": false}, {"id": 3, "answer": "<p>Elective outpatient surgery, including open carpal tunnel release, open reduction of the perilunate dislocation, repair of the scapholunate ligament, and intercarpal pinning</p>", "question_id": 1, "is_correct_answer": false}, {"id": 4, "answer": "<p>Emergent surgery, including open reduction of the perilunate dislocation, repair of the scapholunate ligament, and intercarpal pinning</p>", "question_id": 1, "is_correct_answer": false}, {"id": 5, "answer": "<p>Emergent surgery, including open reduction of the perilunate dislocation, repair of the scapholunate ligament, and intercarpal pinning</p>", "question_id": 1, "is_correct_answer": false}], "item_id": 1, "question": "<p style=\\"text-align: justify\\">Figures 1 and 2 show the postreduction radiographs obtained from a 32-year-old man who fell from a ladder onto his outstretched right arm. He reports right wrist pain and dense numbness in his radial digits. What is the most appropriate treatment option?&nbsp;</p>", "is_random": true}], "attachments": [{"id": "e638f3b9-006a-4021-8c26-425fe3da7bc8", "mime": "png", "path": "attachments/VxgOjk3W-jOqoGMG7xD.png"}, {"id": "48eb006d-20c2-4d4a-81b5-374ebf5f7c27", "mime": "png", "path": "attachments/VxgOjk3W-mgUjQvu73w.png"}], "is_vignette": false}, {"id": 2, "type": {"name": "Multiple Choice", "value": "multiple-choice", "preference": {"is_visible": true, "description": null, "has_answers": true, "is_scoreable": true}}, "order": 2, "title": "Test 02-0411", "content": null, "is_random": true, "questions": [{"id": 3, "type": {"name": "multiple-choice", "label": "Multiple Choice", "is_visible": true, "description": null, "has_answers": true, "is_scoreable": true}, "order": 1, "score": 100, "answers": [{"id": 11, "answer": "<p>Osteosarcoma</p>", "question_id": 3, "is_correct_answer": false}, {"id": 12, "answer": "<p>Enchondroma</p>", "question_id": 3, "is_correct_answer": false}, {"id": 13, "answer": "<p>Fibrous dysplasia</p>", "question_id": 3, "is_correct_answer": false}, {"id": 14, "answer": "<p>Chondrosarcoma</p>", "question_id": 3, "is_correct_answer": true}, {"id": 15, "answer": "<p>Chondromyxoid fibroma</p>", "question_id": 3, "is_correct_answer": false}], "item_id": 2, "question": "<p>A 51-year-old woman has shoulder pain after a minor fall. A radiograph, MRI scan, are seen in Figures 1 through 2. Biopsy specimens are seen in Figures 3. What is the most likely diagnosis?</p>", "is_random": true}], "attachments": [{"id": "520f849f-42d5-4df2-8ee6-b85299a718ee", "mime": "png", "path": "attachments/qzKoYkDo-GITJLRVB6F.png"}, {"id": "8512c878-0158-40a0-852f-f2ca436963fa", "mime": "png", "path": "attachments/qzKoYkDo-GhSzNWtCsa.png"}, {"id": "f7b7e186-56d0-4ea8-a420-56d9b16244a9", "mime": "png", "path": "attachments/qzKoYkDo-zUbJ4hiRy7.png"}], "is_vignette": false}, {"id": 3, "type": {"name": "Multiple Choice", "value": "multiple-choice", "preference": {"is_visible": true, "description": null, "has_answers": true, "is_scoreable": true}}, "order": 3, "title": "Test 03-0411", "content": null, "is_random": true, "questions": [{"id": 4, "type": {"name": "multiple-choice", "label": "Multiple Choice", "is_visible": true, "description": null, "has_answers": true, "is_scoreable": true}, "order": 1, "score": 100, "answers": [{"id": 16, "answer": "<p>MRI and biopsy from an orthopedic oncologist</p>", "question_id": 4, "is_correct_answer": false}, {"id": 17, "answer": "<p>Marginal resection with complete removal of cartilage cap</p>", "question_id": 4, "is_correct_answer": true}, {"id": 18, "answer": "<p>Educate the family about the benign nature of the condition and prescribe NSAIDs until the pain diminishes</p>", "question_id": 4, "is_correct_answer": false}, {"id": 19, "answer": "<p>Referral to genetics for screening for potential associated malignancies</p>", "question_id": 4, "is_correct_answer": false}, {"id": 20, "answer": "<p>Intralesional curettage and adjuvant therapy</p>", "question_id": 4, "is_correct_answer": false}], "item_id": 3, "question": "<p>A skeletally-mature 14-year-old girl presents with her parents to your clinic with a \\"lump\\" near her knee. She is very bothered by the appearance of her knee and it is very painful when she bumps the palpable prominence. An AP x-ray is shown in figures 1, respectively. She has no other similar lesions elsewhere in her body, and her parents are unaware of any relevant family history. What is the next best step in management?&nbsp;</p>", "is_random": true}], "attachments": [{"id": "5f74bb4e-0aeb-478a-80a7-178f3522907a", "mime": "png", "path": "attachments/YRgyGB68-wmY9h91aWl.png"}], "is_vignette": false}, {"id": 723, "type": {"name": "Multiple Choice", "value": "multiple-choice", "preference": {"is_visible": true, "description": null, "has_answers": true, "is_scoreable": true}}, "order": 4, "title": "Test 04-0411", "content": null, "is_random": true, "questions": [{"id": 1055, "type": {"name": "multiple-choice", "label": "Multiple Choice", "is_visible": true, "description": null, "has_answers": true, "is_scoreable": true}, "order": 1, "score": 100, "answers": [{"id": 26, "answer": "<p style=\\"text-align: justify\\">A positive Lhermitte sign.</p>", "question_id": 1055, "is_correct_answer": true}, {"id": 27, "answer": "<p style=\\"text-align: justify\\">A positive Spurling sign.</p>", "question_id": 1055, "is_correct_answer": false}, {"id": 28, "answer": "<p style=\\"text-align: justify\\">A positive Jackson sign.</p>", "question_id": 1055, "is_correct_answer": false}, {"id": 29, "answer": "<p style=\\"text-align: justify\\">A positive Lasegue sign.</p>", "question_id": 1055, "is_correct_answer": false}, {"id": 30, "answer": "<p style=\\"text-align: justify\\">A positive Hoffmann sign.</p>", "question_id": 1055, "is_correct_answer": false}], "item_id": 723, "question": "<p style=\\"text-align: justify\\">A 63-year-old man has a feeling of generalized clumsiness in his arms and hands, difficulty buttoning his shirt, and gradually worsening gait instability. During examination, his neck is gently passively flexed to end range while he is seated. The patient describes an electric shock-like sensation that radiates down the spine and into the extremities. This describes which of the following?&nbsp;</p>", "is_random": true}], "attachments": [], "is_vignette": false}], "is_mcq": true, "exam_id": 43, "metadata": {"total_items": 4, "total_questions": 4, "snapshot_created_at": "2025-11-04T21:32:36+00:00"}, "exam_code": "TEST25", "exam_name": "TEST25", "is_random": false, "is_interview": false}	4	4	2025-11-04 21:32:36	2025-11-04 21:32:36
\.


--
-- Data for Name: delivery_taker; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.delivery_taker (delivery_id, taker_id, token, is_login) FROM stdin;
53	193	VRKJH	f
53	194	QIXZ7	f
53	195	KUJ2W	f
53	196	X5KRW	f
53	197	R1HSE	f
53	198	AEUQL	f
53	199	PENFO	f
53	210	YCXCN	f
53	235	LN7VJ	f
53	265	MVC7G	f
53	266	DUHCS	f
53	307	KJBGA	f
53	308	D0QHN	f
53	309	NMITS	f
53	310	ZJDP1	f
53	311	0TJ5F	f
54	227	MXFOW	f
54	228	SXMFA	f
54	229	FKNP2	f
54	231	TLL7X	f
54	243	HJM4V	f
54	244	I39YU	f
54	245	4OLYQ	f
54	246	T8KN7	f
54	242	IHLBM	f
54	247	MUDET	f
54	253	7PV64	f
54	255	LFMSR	f
54	256	DW74Z	f
54	257	9FFRU	f
54	258	RCQB4	f
54	280	IDGZA	f
54	281	SSMS2	f
54	282	NQMDK	f
54	283	3QAR9	f
54	284	BUFJC	f
54	301	PO4SL	f
54	302	TFPM3	f
54	313	NOUQC	f
57	193	DDSIH	f
57	194	VWAPG	f
57	195	3XZ1G	f
57	196	NSXP1	f
57	197	N5I5E	f
57	198	63KGL	f
57	199	4BCBP	f
57	210	H1WFF	f
57	235	0BXIJ	f
57	265	LPXAS	f
57	266	64TYV	f
57	307	LHWOW	f
57	308	1ONEJ	f
57	309	UVREC	f
57	310	0B19L	f
57	311	S36DW	f
58	227	ZFMPH	f
58	228	NGFXX	f
58	229	2NH33	f
58	231	HV3WN	f
58	243	WWRL3	f
58	244	CQZP8	f
58	245	HCE6M	f
58	246	DROBH	f
58	242	NQ6GG	f
58	247	UKHLB	f
58	253	5EVVY	f
58	255	C4J3C	f
58	256	3PFVS	f
58	257	ASTZS	f
58	258	E9XIX	f
58	280	G8S34	f
58	281	JNCMQ	f
58	282	8KCD9	f
58	283	WM8SS	f
58	284	LRR0B	f
58	301	PZHGC	f
58	302	53FG9	f
58	313	GAJEM	f
61	413	EXGWA	f
61	414	XYHH4	f
61	415	PYXEC	f
61	416	YDH2O	f
61	417	IGJBF	f
61	418	XZ2HF	f
61	279	RE6R8	f
61	278	VVS0A	f
61	277	SZOUB	f
61	276	AZCES	f
61	419	DC22B	f
62	413	Q3PWY	f
62	414	HUNJW	f
62	415	7SPKB	f
62	416	FVPUC	f
62	417	7PL7P	f
62	418	FBTBK	f
62	279	2EIEM	f
62	278	TJWRC	f
62	277	QEYBD	f
62	276	P8I8L	f
62	419	CSWLR	f
63	420	BYJA0	f
64	420	A855A	f
65	413	FMJGP	f
65	414	KH262	f
65	415	WN3JI	f
65	416	YJFPK	f
65	417	PB2MG	f
65	418	TFC13	f
65	279	4HHXL	f
65	278	3PGHZ	f
65	277	TYTJ0	f
65	276	XXR51	f
65	419	HEOEP	f
66	413	1P9RK	f
66	414	VGMLL	f
66	415	W4QVH	f
66	416	MXKKU	f
66	417	L2LAD	f
66	418	D1XNT	f
66	279	IXDLO	f
66	278	GFFLQ	f
66	277	HU3CG	f
66	276	JS6ZY	f
66	419	IBDDY	f
67	413	YDINP	f
67	414	10RJ4	f
67	415	IMGWT	f
67	416	U4DFO	f
67	417	QOVHI	f
67	418	TINJK	f
67	279	0U4OM	f
67	278	MOYNO	f
67	277	2HLZH	f
67	276	RLV2P	f
67	419	JKUZS	f
68	422	VD4KB	f
68	423	JKAOO	f
68	424	SZ2YA	f
68	425	XVBYA	f
68	426	10JYV	f
68	427	NRRPG	f
68	428	CYZ5V	f
68	429	OAIS7	f
68	430	HCYNO	f
68	431	NVHKL	f
68	432	QBY6B	f
68	433	ZJD5L	f
68	434	GMEQ3	f
68	435	GGX4W	f
68	436	JZBLL	f
68	437	OGAYC	f
68	438	IKMNP	f
68	439	XQAZO	f
68	440	8RLQ4	f
68	441	V9DRJ	f
68	442	GTCZN	f
68	443	GVYJS	f
68	444	RM0VU	f
68	445	JODGK	f
68	446	EX20L	f
68	447	2XLMV	f
68	448	YG0SO	f
68	449	LHYFB	f
68	450	VBKEJ	f
68	451	KQ8TR	f
68	452	OZPE2	f
68	453	PQ646	f
68	454	A6WBY	f
68	455	RWUUH	f
68	456	VIHII	f
68	457	ELEDS	f
68	458	WCWO3	f
68	459	6PHQZ	f
68	460	PEF12	f
68	461	S5IWB	f
68	261	ALNEH	f
68	262	N1DYU	f
68	263	WYEVN	f
68	264	I8GV9	f
68	413	EUYDS	f
68	277	FSCKG	f
68	415	DBB2A	f
68	417	PETSQ	f
68	278	0ZUBR	f
68	279	CDOOH	f
68	418	HZG8J	f
68	419	MBLM5	f
68	276	DFPNT	f
68	416	28XTO	f
68	244	L6NQI	f
68	267	VSFTF	f
68	268	6GIPF	f
68	269	EFSAZ	f
68	270	TJJ66	f
68	271	V530U	f
68	272	MIUU7	f
68	273	D7XMB	f
68	274	CMJ0D	f
68	275	QNSGI	f
68	285	QOYTL	f
68	286	SMW3Q	f
68	287	MMMF2	f
68	288	XQMYH	f
68	289	J7KU8	f
68	290	0KYXM	f
68	291	ZULT5	f
68	292	DFQ2G	f
68	294	SJL2H	f
68	295	C3JSC	f
68	296	U6PQX	f
68	293	GP0ZW	f
68	297	SZBO1	f
68	298	Y6O0I	f
68	299	YXHG3	f
68	300	GG7JY	f
68	304	T5DDF	f
68	305	BD8PO	f
68	306	PICHL	f
68	259	1VEAL	f
68	310	DJ080	f
69	422	WHOCN	f
69	423	CXDBA	f
69	424	RZFGA	f
69	425	0FCAA	f
69	426	UKEHI	f
69	427	XWRCW	f
69	428	GEFCX	f
69	429	CBQDU	f
69	430	8RTV4	f
69	431	ECJ9K	f
69	432	MQE20	f
69	433	F5CTG	f
69	434	SB3XQ	f
69	435	EAVJE	f
69	436	LY5TA	f
69	437	AGNCV	f
69	438	5SYUF	f
69	439	UKCXU	f
69	440	07VGA	f
69	441	IZBXG	f
69	442	Q33SV	f
69	443	EDVSC	f
69	444	0LGZZ	f
69	445	0O3J9	f
69	446	T5QQE	f
69	447	XWKVZ	f
69	448	PG4DQ	f
69	449	RRUSG	f
69	450	PH9JF	f
69	451	MCOKZ	f
69	452	EGQFL	f
69	453	U9ACG	f
69	454	P9AUK	f
69	455	BUTQN	f
69	456	9VKD5	f
69	457	VZDIG	f
69	458	MDANE	f
69	459	BOXGM	f
69	460	UYGWB	f
69	461	HQP4I	f
69	261	LOFGJ	f
69	262	ZV91Q	f
69	263	6LA2T	f
69	264	DO2ER	f
69	413	PZ6FR	f
69	277	QXMCL	f
69	415	UM85B	f
69	417	G8YXI	f
69	278	SZVMP	f
69	279	KXYAP	f
69	418	ADI5K	f
69	419	PVROP	f
69	276	6ASVN	f
69	416	LWEBG	f
69	244	RPVBK	f
69	267	A9UER	f
69	268	OJLZI	f
69	269	4KEC0	f
69	270	R7OQ4	f
69	271	AUEWP	f
69	272	HSP3T	f
69	273	4L02C	f
69	274	QSINE	f
69	275	ORJPS	f
69	285	BEVBW	f
69	286	59GUM	f
69	287	QVEVS	f
69	288	B7EQN	f
69	289	LTD9Q	f
69	290	FPYM3	f
69	291	1107V	f
69	292	HRPCB	f
69	294	NYYGD	f
69	295	0IIWV	f
69	296	ZJRIB	f
69	293	WXWUK	f
69	297	APO2X	f
69	298	Y3HW2	f
69	299	SAUJA	f
69	300	G46JJ	f
69	304	9YIVR	f
69	305	NKKHD	f
69	306	DHBUP	f
69	259	AYP6D	f
69	310	D056F	f
70	462	XXHQA	f
71	462	GZEXG	f
72	268	0UVF7	f
72	267	NFZXA	f
72	269	EZYPD	f
72	270	HB1JW	f
72	271	TFALB	f
72	272	M6REJ	f
72	273	GRLLM	f
72	274	EA6QJ	f
72	275	C7JBN	f
72	261	TQTLX	f
72	262	FYRFF	f
72	263	KGSKY	f
72	264	QSVMY	f
72	290	RWNHL	f
72	295	2A5ZT	f
72	296	JF8LD	f
72	294	6K1XJ	f
72	292	WRTH5	f
72	310	D034H	f
72	291	XDWLW	f
72	289	WUP05	f
73	413	TVPSQ	f
73	442	Y7OI4	f
73	285	RIO2R	f
73	297	LIM7A	f
73	304	XVMPJ	f
73	415	ZGED0	f
73	443	IIRQV	f
73	287	OCLMA	f
73	417	UQKUE	f
73	444	ZZZCM	f
73	286	0TSFV	f
73	278	GM0OA	f
73	445	4E8BJ	f
73	298	SAKGT	f
73	279	GACDQ	f
73	299	8FEAS	f
73	305	AFZKI	f
73	418	VPFFY	f
73	300	UJP4N	f
73	419	4QTBX	f
73	306	4R36F	f
73	277	DU97L	f
74	268	JPYG5	f
74	267	GFCZ7	f
74	269	4CTJR	f
74	270	5HAFH	f
74	271	2ETEI	f
74	272	RKRQY	f
74	273	NY6ZH	f
74	274	9BB9E	f
74	275	NIUTT	f
74	261	TXMDW	f
74	262	8FOAS	f
74	263	HRTA6	f
74	264	BJOHP	f
74	290	7HWLY	f
74	295	K3WM3	f
74	296	RGRM7	f
74	294	U8UOU	f
74	292	C2UBO	f
74	310	UOIUE	f
74	291	RI4UT	f
74	289	1N1TE	f
75	413	RRQG0	f
75	442	TXTH6	f
75	285	RBYFO	f
75	297	DA8MX	f
75	304	3Y2JU	f
75	415	4ZVFY	f
75	443	WR6FU	f
75	287	A0BYP	f
75	417	LD62J	f
75	444	68GCJ	f
75	286	XHGF3	f
75	278	MLU40	f
75	445	V2MNS	f
75	298	NYUQX	f
75	279	RJIPD	f
75	299	MRYQN	f
75	305	AVM3T	f
75	418	PR2AG	f
75	300	J8OD8	f
75	419	ANZRC	f
75	306	RYEQZ	f
75	277	GPIFU	f
68	464	ANIUZ	f
68	414	AVBNV	f
68	462	AIZOA	f
68	465	9EOTM	f
68	466	LS06I	f
68	467	0LTIZ	f
68	468	0SBST	f
68	469	MWY1N	f
68	470	ZSY7D	f
68	471	XOG2W	f
68	472	USG9I	f
68	473	LGYPG	f
68	474	A9UI9	f
68	475	3CUCH	f
68	476	UPGCU	f
68	477	XACA0	f
68	478	JXWLE	f
68	479	JJC4S	f
68	480	N7KBX	f
68	481	CUXYM	f
68	482	AM7JF	f
68	483	FTMJQ	f
68	484	FDISI	f
68	485	AT6ZD	f
68	486	HKDEM	f
68	487	UGWCR	f
68	488	HMTRD	f
68	490	IW5UY	f
68	491	YNCED	f
68	492	MLIMV	f
68	493	QTA89	f
68	494	FBAOA	f
68	495	ZOYTV	f
68	497	VSTOD	f
68	498	Q8EXQ	f
68	499	PEPBG	f
68	502	FKNIP	f
68	503	RR4OV	f
68	504	LYHC7	f
68	505	GEIE4	f
68	506	I8AYY	f
68	507	ZPXVW	f
68	508	P7ONW	f
68	509	LALCM	f
76	422	R0CLZ	f
76	423	BSN7U	f
76	424	AVLKR	f
76	425	NIGAT	f
76	426	O2Q6Q	f
76	427	V9L34	f
76	428	5CGTL	f
76	429	WRJWH	f
76	430	FWW3X	f
76	431	ZVLXX	f
76	432	CIGJM	f
76	433	MTLJY	f
76	434	XJAYD	f
76	435	LLJOG	f
76	464	TKH1F	f
76	276	9ERLN	f
76	416	RWNQA	f
76	414	ZDNVB	f
76	436	2LXBD	f
76	437	QYWP9	f
76	462	LVAPV	f
76	465	QNRG2	f
76	439	TTUMP	f
76	438	OMTL8	f
76	466	ZZCEK	f
76	467	JQ1V7	f
76	268	ZTCDP	f
76	440	O9OMI	f
76	441	TU62H	f
76	468	GWP2F	f
76	469	DJQ5Q	f
76	470	CKWFL	f
76	471	PNPPJ	f
76	472	NW8RY	f
76	473	ONW7U	f
76	474	DRE3W	f
76	475	KYKUZ	f
76	476	4JUW7	f
76	477	VBEIH	f
76	478	NAYT0	f
76	479	HVFZX	f
76	480	PWKJ0	f
76	481	1DQ4W	f
76	482	CBIRN	f
76	483	HEBLP	f
76	484	K3SH6	f
76	288	0MX1U	f
76	447	OGRHG	f
76	485	DOZSV	f
76	486	S7ER5	f
76	487	SZOFI	f
76	488	MNYRT	f
76	450	2QT6C	f
76	451	CTS5N	f
76	454	F07M0	f
76	455	HFXFR	f
76	293	SGTJF	f
76	490	9L5LC	f
76	491	NXCXR	f
76	492	M2WQX	f
76	493	5W1HD	f
76	494	RYSA7	f
76	495	JDLUT	f
76	456	BWSVD	f
76	457	LAGG4	f
76	458	XHVM2	f
76	497	VX2KH	f
76	498	C5OQQ	f
76	499	UYK2K	f
76	259	FIMVG	f
76	459	Z7IWZ	f
76	502	DJSUE	f
76	503	2LS9A	f
76	504	N6E6A	f
76	505	TKGFH	f
76	506	T9MBJ	f
76	507	ITQ5A	f
76	508	A1SVN	f
76	509	X9G1Y	f
76	510	IQ5SQ	f
76	453	TNQAU	f
77	422	8IYAV	f
77	423	2WFJ2	f
77	424	DCOUJ	f
77	425	BWNPS	f
77	426	MPWDL	f
77	427	8LCVY	f
77	428	Y6HAY	f
77	429	YGWAL	f
77	430	RMX5L	f
77	431	CP5J5	f
77	432	VVBMM	f
77	433	GHNBK	f
77	434	EGUMG	f
77	435	263YT	f
77	464	VK6WZ	f
77	276	ABCNY	f
77	416	WDDK5	f
77	414	SSFMY	f
77	436	ZTP9H	f
77	437	SE1TX	f
77	462	0JTSL	f
77	465	GXJV9	f
77	439	YN2V4	f
77	438	KDFHL	f
77	466	CDIM0	f
77	467	KABAU	f
77	268	SBK2T	f
77	440	Q769K	f
77	441	WMRG9	f
77	468	OUOBY	f
77	469	4NV71	f
77	470	WIRNH	f
77	471	8WERJ	f
77	472	RXRU1	f
77	473	EW00L	f
77	474	USJ1M	f
77	475	JM0TH	f
77	476	PRSV7	f
77	477	YMBMU	f
77	478	YWG5V	f
77	479	ZTA5F	f
77	480	XH0MG	f
77	481	VZRB0	f
77	482	YD0V4	f
77	483	YKRUW	f
77	484	JPERC	f
77	288	4MBJA	f
77	447	ZNOHD	f
77	485	YVRNF	f
77	486	SBRMB	f
77	487	R424D	f
77	488	SVDZH	f
77	450	BKUYN	f
77	451	3XJOO	f
77	454	9DI5K	f
77	455	MW7TH	f
77	293	2EGBZ	f
77	490	SPCM3	f
77	491	F5LZ6	f
77	492	Q7IB5	f
77	493	LWDLA	f
77	494	O5G2R	f
77	495	FZ0RJ	f
77	456	OBXAN	f
77	457	KCZEI	f
77	458	X7O22	f
77	497	8Y6SI	f
77	498	UEJAK	f
77	499	SBCON	f
77	259	LWGOA	f
77	459	VYOMD	f
77	502	S0ZXO	f
77	503	A7GWK	f
77	504	W9RAY	f
77	505	CWPO6	f
77	506	LGLUW	f
77	507	VXVXA	f
77	508	BTKKU	f
77	509	O56YO	f
77	510	T3LF4	f
77	453	DGSPL	f
78	276	AW2FA	f
78	436	0NNQ9	f
78	416	FWAFG	f
78	462	O3RER	f
78	437	SVGIK	f
78	439	XQPAS	f
78	414	QZI23	f
78	465	XQGKM	f
78	479	AKEII	f
78	480	SAEZU	f
78	481	ZCMVX	f
78	482	AMMV7	f
78	483	HPCZB	f
78	484	5YTPD	f
78	288	UQY3K	f
78	447	3EU1Q	f
78	458	XVTHB	f
78	457	TSTUQ	f
78	456	UHZSL	f
78	459	E7TRB	f
78	259	G3B19	f
78	460	BHKVX	f
79	276	VSYKD	f
79	436	YD2PA	f
79	416	TWHKZ	f
79	462	YBJTY	f
79	437	OOL1G	f
79	439	BZAHH	f
79	414	TVXBR	f
79	465	CLTSD	f
79	479	MGCZZ	f
79	480	LQHUZ	f
79	481	PW6AH	f
79	482	BTHWU	f
79	483	MJRYU	f
79	484	JTPPU	f
79	288	SECPH	f
79	447	HSQ2M	f
79	458	OK5JD	f
79	457	ROKRK	f
79	456	H2GXK	f
79	459	S2KKC	f
79	259	4VPSG	f
79	460	48KGI	f
80	424	SO71M	f
80	427	BCKNU	f
80	450	OYJHL	f
80	455	OQMFT	f
80	425	WXX3X	f
80	426	Z8V2N	f
80	428	ZFHWW	f
80	422	ONNY8	f
80	293	NTZUI	f
80	454	CQPZN	f
80	429	MCED7	f
80	451	9WW0Z	f
80	441	8FIHQ	f
80	268	BDVOL	f
80	506	LGBNI	f
80	423	RMLKP	f
80	430	WO8TZ	f
80	468	DYGR0	f
80	440	Q0EEK	f
80	431	QALKF	f
80	432	GJMLZ	f
80	453	NIJ0P	f
80	469	H44X7	f
80	433	MQJC1	f
80	434	ETS1C	f
80	435	FFMWB	f
81	424	3KJ4H	f
81	427	GD52S	f
81	450	SIGW7	f
81	455	4V5Q2	f
81	425	DZCSW	f
81	426	EY8NS	f
81	428	XREFX	f
81	422	Y2FUV	f
81	293	AMB8K	f
81	454	K3LNI	f
81	429	FIOE3	f
81	451	B3PKY	f
81	441	URIFS	f
81	268	XUGJC	f
81	506	RMR9E	f
81	423	GUALW	f
81	430	NAVJ5	f
81	468	C7LLD	f
81	440	LTKCG	f
81	431	GTDFU	f
81	432	OAIBU	f
81	453	UGEU6	f
81	469	JWDNW	f
81	433	Q1HLR	f
81	434	HCKSM	f
81	435	PFGB3	f
82	314	TSUAD	f
82	315	IUHPV	f
82	316	R05X6	f
82	317	6CFIA	f
82	318	JEEMA	f
82	319	W0DJT	f
82	320	EYRVD	f
82	321	7HZVU	f
82	322	65OLY	f
82	323	JBZRO	f
82	324	ZSPPR	f
82	325	1H726	f
82	326	HOE87	f
82	327	A9KLF	f
82	328	PHMT2	f
82	329	Q8MOG	f
82	330	HMP7B	f
82	331	1VOCJ	f
82	332	O2MBE	f
82	333	F8NDT	f
82	334	0BBHH	f
82	335	9X9KQ	f
82	336	UUZV8	f
82	337	ASC45	f
82	338	ER6U7	f
82	339	RURSL	f
82	340	XWCDV	f
82	341	YO4DF	f
82	342	PSLGW	f
82	343	SNTMQ	f
82	344	TNEN8	f
82	345	0BCU6	f
82	346	2FBRA	f
82	347	V6KS6	f
82	348	934XL	f
82	349	IFYLZ	f
82	350	JFC3F	f
82	351	YGZMT	f
82	352	JGFSG	f
82	353	LGFUS	f
82	354	X6U6V	f
82	355	JG48N	f
82	356	3VRMW	f
82	357	9FTSE	f
82	358	F5HJL	f
82	359	CMLAK	f
82	360	ADKZB	f
82	361	3L5IU	f
82	362	NS0HS	f
82	363	IVTTF	f
82	364	30ETF	f
82	365	GYFOS	f
82	366	37AFC	f
82	367	NJAOO	f
82	368	GRA0H	f
82	369	BCBJB	f
82	370	S0U6D	f
82	371	EEXVR	f
82	372	WZ3J7	f
82	373	ZOUQN	f
82	374	CDICD	f
82	375	A6CP9	f
82	376	1EIKK	f
82	377	31URL	f
82	378	KFELB	f
82	379	JMWVS	f
82	380	GG4LY	f
82	381	AA6WT	f
82	382	FEHYQ	f
82	383	ATECI	f
82	384	VMYYR	f
82	385	ETN1C	f
82	386	VHDQX	f
82	387	IJVRP	f
82	388	CAYSC	f
82	389	TLQ3Q	f
82	390	LVWMP	f
82	391	8GHVD	f
82	392	REB5X	f
82	393	VLY6Z	f
82	394	RO5FE	f
82	395	IEURH	f
82	396	LYIL1	f
82	397	G9RGW	f
82	398	8GZC6	f
82	399	QKJE9	f
82	400	4GBFC	f
82	401	7MZKW	f
82	402	YZEWP	f
82	403	VJ2OX	f
82	404	LDOPA	f
82	405	45ECD	f
82	406	WJLQQ	f
82	407	MJI3E	f
82	408	C1DBN	f
82	409	Y4MQW	f
82	410	DE74V	f
82	411	CGBPD	f
82	412	TAYJF	f
83	511	HSZ0O	f
83	512	8XML2	f
83	513	SH2LC	f
83	514	GWOLM	f
83	515	VBODN	f
83	516	6XMHZ	f
83	464	C2N8W	f
84	467	EXFZD	f
84	539	UZMPO	f
84	540	ADXPF	f
84	466	ZJQGC	f
84	438	ISKPD	f
84	541	KUNWM	f
84	542	DRCGY	f
84	543	SR1TQ	f
84	544	IB8CU	f
84	545	OENZD	f
84	546	WZSCS	f
85	268	TL6O0	f
85	470	DM4LX	f
85	471	P65UH	f
85	472	GTGTF	f
85	473	VXTM5	f
85	474	A7V2N	f
85	475	MHGZB	f
85	476	N5SVC	f
85	477	PW6JA	f
85	478	Z6ANV	f
85	517	MLE5X	f
85	518	BJDEV	f
85	519	TLEWJ	f
85	520	FCPRS	f
85	521	YQ7DV	f
86	522	XLSOW	f
86	523	MSNJM	f
86	524	3AG9R	f
86	525	DYB0E	f
86	526	WMJ3R	f
86	527	K3C6F	f
86	561	D7CJY	f
87	485	K3GVL	f
87	488	HZBAQ	f
87	486	60TUD	f
87	528	IIMH6	f
87	529	IBUYZ	f
87	530	F2IQF	f
87	531	BALQN	f
87	532	HPGG7	f
88	490	J73VA	f
88	494	NKNJF	f
88	495	V4PWL	f
88	493	BZYAW	f
88	492	W9LYK	f
88	491	ARHNG	f
88	536	D5G7Y	f
88	537	RVKIN	f
88	538	N6FDA	f
89	497	EZRG7	f
89	498	KGHEJ	f
89	547	UKRCM	f
89	548	ZP0Y9	f
89	549	TWDOU	f
89	550	7GJHX	f
89	551	QQ9ZF	f
89	552	IYTRM	f
89	553	75SYR	f
89	554	WMJN9	f
89	555	SV0QV	f
89	556	REEOC	f
90	502	T5ELG	f
90	503	H8VHJ	f
90	504	Q3UD1	f
90	505	UPLYA	f
90	557	JLZQ2	f
90	558	IFXPJ	f
91	507	XFLWQ	f
91	508	AFDJC	f
91	559	KUJ6J	f
93	511	IKZ3U	f
93	512	1OMMB	f
93	513	IHWZV	f
93	514	OU6MY	f
93	515	JS5OC	f
93	516	INBJF	f
93	464	XMKAF	f
94	467	DONUW	f
94	539	WM3OR	f
94	540	PXIXE	f
94	466	VUEUY	f
94	438	7DRXA	f
94	541	KTQ9C	f
94	542	24JVE	f
94	543	YRHSP	f
94	544	Q9ET2	f
94	545	9R1IL	f
94	546	TXGCN	f
95	268	6BY5S	f
95	470	FZPOC	f
95	471	PU7GU	f
95	472	FIYZJ	f
95	473	T5LOY	f
95	474	FYAFL	f
95	475	6CF5G	f
95	476	639EH	f
95	477	WDZHU	f
95	478	2FCMQ	f
95	517	EPF45	f
95	518	ZVZQR	f
95	519	EA2ZX	f
95	520	8WQTV	f
95	521	N4LOB	f
96	522	ZZHJT	f
96	523	6L5GP	f
96	524	NNKIQ	f
96	525	Q30NB	f
96	526	LFFTV	f
96	527	XMZBL	f
96	561	TSKMO	f
97	490	VZBJH	f
97	494	WMBVX	f
97	495	MUZFA	f
97	493	YFGEI	f
97	492	TBWA8	f
97	491	W8DBO	f
97	536	PN0IR	f
97	537	ZZUY6	f
97	538	HJ64Q	f
98	485	VZCVV	f
98	488	LQU58	f
98	486	W93KA	f
98	528	XONLC	f
98	529	NBWZC	f
98	530	VXZIH	f
98	531	USUDT	f
98	532	YYQVR	f
99	502	VJFMI	f
99	503	KR5P4	f
99	504	OR3K7	f
99	505	JZZ02	f
99	557	QACWF	f
99	558	85YV9	f
100	507	2AA5U	f
100	508	H2SX3	f
100	559	0FJVQ	f
101	497	6FGJR	f
101	498	FBSQU	f
101	547	MXKY0	f
101	548	F8RMP	f
101	549	OTJ4L	f
101	550	L5ZOL	f
101	551	IJG1A	f
101	552	YWF3T	f
101	553	HELVB	f
101	554	NT7KI	f
101	555	1NFAF	f
101	556	UZXWZ	f
103	449	HHCYE	f
103	485	OGIBB	f
103	488	HAHML	f
103	524	AVTNX	f
103	505	YPF9A	f
103	486	VAMBD	f
103	467	MKEMJ	f
103	539	S0BPI	f
103	540	SXCVB	f
103	502	KBLXD	f
103	504	60E8X	f
103	503	51KIB	f
103	528	1YEPK	f
103	466	MTVDG	f
103	523	J4EUA	f
103	522	2XPSP	f
103	525	LGEG7	f
104	477	IQPQQ	f
104	494	NUF2Y	f
104	509	1RXJL	f
104	464	Q8S0X	f
104	517	QFMRG	f
104	476	9Q61P	f
104	491	QW494	f
104	495	JG3VW	f
104	470	W861N	f
104	511	JB6CL	f
104	508	HJXZA	f
104	478	M8GPH	f
104	268	RSQ0U	f
104	520	C0QK4	f
104	493	UB7AE	f
104	510	OUREP	f
104	518	XRJJT	f
104	490	A5M9Y	f
104	513	CKKXB	f
104	519	IXAMB	f
104	507	8GWQX	f
104	472	RDAUP	f
104	473	F0HSE	f
104	471	IMCFM	f
104	514	IFJVS	f
104	474	BGQ9B	f
104	452	89W5S	f
104	475	VA7EB	f
104	492	L31HW	f
105	449	EABR0	f
105	485	MEYCL	f
105	488	P6C3U	f
105	524	C9BCL	f
105	505	TRQY3	f
105	486	PLI99	f
105	467	D9WAL	f
105	539	IYFMF	f
105	540	UO3KG	f
105	502	AXZ1P	f
105	504	W4X1V	f
105	503	AMZEL	f
105	528	YEPQJ	f
105	466	AKJHL	f
105	523	XZCSX	f
105	522	WNIXW	f
105	525	WVUYE	f
106	477	BFS9D	f
106	494	9KW24	f
106	509	VOXZ7	f
106	464	3T7YP	f
106	517	46GGA	f
106	476	HYDT8	f
106	491	XQF4F	f
106	495	XK1AY	f
106	470	DYILM	f
106	511	IKION	f
106	508	EARKN	f
106	478	AZLBE	f
106	268	VJCHW	f
106	520	LYKVS	f
106	493	5AVVS	f
106	510	DNCR7	f
106	518	CAO16	f
106	490	MLLXJ	f
106	513	MGNUN	f
106	519	EUOPF	f
106	507	NGOBP	f
106	472	ONBPZ	f
106	473	UHI0Y	f
106	471	CO4F4	f
106	514	A0XZB	f
106	474	D6MHF	f
106	452	YABJT	f
106	475	U2I4G	f
106	492	MK7LX	f
107	564	N4EXL	f
107	565	L3JDU	f
107	566	ZQNWR	f
107	567	VC4TR	f
107	568	CBVYD	f
107	569	JMP8O	f
107	570	R9S3R	f
107	571	MGWCI	f
107	572	J5FUP	f
108	564	J8D2A	f
108	565	BI98T	f
108	566	B9RNE	f
108	567	JJ0DY	f
108	568	QNPAW	f
108	569	ZFHG0	f
108	570	VMUCV	f
108	571	ODZ8M	f
108	572	Z9HXW	f
109	573	GNL2D	f
109	574	NUQZB	f
109	575	5O1LL	f
109	576	RZEG4	f
109	577	LGSEX	f
109	578	VW6XC	f
109	580	XP2GC	f
109	582	EJ4KY	f
109	583	86AJP	f
109	584	HYFQK	f
109	585	1KD7H	f
109	586	UT1P2	f
109	591	X8EXD	f
109	438	SIRXG	f
109	592	7LBLP	f
109	595	R3Q0T	f
109	597	VR1WF	f
109	598	TVFRL	f
109	599	KYR1H	f
109	600	JZNRN	f
109	601	QABRD	f
109	602	YIQG6	f
109	603	WAWN7	f
109	604	QFAYY	f
109	605	XVIH7	f
109	606	EFHR4	f
109	607	PX69Z	f
109	531	KGDWS	f
109	532	POM0M	f
109	530	RAGQE	f
109	536	PRNQZ	f
109	537	SWGAZ	f
109	538	LDKMM	f
109	558	VVW0O	f
109	557	3T8SI	f
109	629	PWK2M	f
109	630	42T7Q	f
109	631	HMJYQ	f
109	632	ZADCR	f
109	633	YF35Y	f
109	634	DFF9W	f
109	636	DZQNZ	f
109	635	ULBI6	f
109	448	CD1HM	f
110	573	IO0RK	f
110	574	JTITQ	f
110	575	JDDFS	f
110	576	OMN2D	f
110	577	QMX3H	f
110	578	BVUIH	f
110	580	HOQT9	f
110	582	MMTDC	f
110	583	M3SJX	f
110	584	BZKGP	f
110	585	V4NIT	f
110	586	G3NI7	f
110	591	WOOTA	f
110	438	AYWX5	f
110	592	TNWOJ	f
110	595	JEVDE	f
110	597	EVNQZ	f
110	598	T07Y3	f
110	599	ZDMQX	f
110	600	0ZBTK	f
110	601	7STKV	f
110	602	1U7BW	f
110	603	MWXGA	f
110	604	UTMAJ	f
110	605	EOVI3	f
110	606	KPWOB	f
110	607	LYFMW	f
110	531	56BCK	f
110	532	DJSCX	f
110	530	WC6LJ	f
110	536	0U2OZ	f
110	537	YRZNY	f
110	538	BL1LL	f
110	558	NCLKO	f
110	557	UQ28C	f
110	629	EZB2I	f
110	630	YDZAL	f
110	631	B4ALS	f
110	632	YX2AT	f
110	633	LWEAP	f
110	634	I8U3D	f
110	636	U05GB	f
110	635	VDTKN	f
110	448	GNPHR	f
111	527	EIOBR	f
111	610	MNXDG	f
111	611	G9Q08	f
111	613	SVH0B	f
111	614	AZBKB	f
111	615	RBI3A	f
111	616	MSMVZ	f
111	617	FGDU2	f
111	497	PN4OG	f
111	498	ZADKF	f
111	548	8Q5P2	f
111	549	A49WP	f
111	550	1LKL8	f
111	554	XGTPR	f
111	556	02IQ0	f
111	624	DJYDO	f
111	625	OH2HF	f
111	626	0DMUF	f
111	627	HUPJ4	f
111	628	4ZMWC	f
112	527	NZMSP	f
112	610	RHFEG	f
112	611	WVA58	f
112	613	RJWLS	f
112	614	V57YU	f
112	615	BSPMQ	f
112	616	2SPNE	f
112	617	F0FSE	f
112	497	UPMSY	f
112	498	VRJWF	f
112	548	BKLCB	f
112	549	KBAVA	f
112	550	KA8IB	f
112	554	EKH0N	f
112	556	ORYM7	f
112	624	MYYZ0	f
112	625	58CQ2	f
112	626	BJVPS	f
112	627	WWWGS	f
112	628	FZGZI	f
109	637	7UBVM	f
109	638	AGKZD	f
109	639	HXN1B	f
109	640	SHESQ	f
109	641	5WDBX	f
109	642	HCDXD	f
109	643	GWXU2	f
110	637	VJ2WR	f
110	638	A8G85	f
110	639	QIAVP	f
110	640	JZMGS	f
110	641	0BSK6	f
110	642	CJHH1	f
110	643	RDYWO	f
113	562	CH5QZ	f
113	529	VKFV3	f
113	574	WIYK7	f
113	575	WXQEQ	f
113	538	YWNYL	f
113	634	2ASKA	f
113	546	ZGC6F	f
113	589	ZSJ9B	f
113	591	LBUUA	f
113	576	HUNUE	f
113	530	QLIT1	f
113	558	IFSZC	f
113	536	X7TJB	f
113	577	1K6IN	f
113	438	JQ1GY	f
113	578	S8BQ1	f
113	531	ODRUS	f
113	516	CBQ3K	f
113	521	XVLIH	f
113	592	HCWBY	f
113	545	X1EAD	f
113	537	SFHER	f
113	573	8RCNJ	f
113	580	HBYHR	f
113	595	CSHXV	f
113	532	CLMA1	f
113	633	ARSLN	f
113	557	HSFLT	f
114	550	X6QU1	f
114	549	NGENF	f
114	498	OPPMR	f
114	554	TGMXO	f
114	497	KNMUS	f
114	527	AIHRQ	f
114	548	RAEBF	f
114	556	BHIPH	f
115	562	ZYSVY	f
115	529	GXJLV	f
115	574	CSM6D	f
115	575	PWKXK	f
115	538	D4BLW	f
115	634	EBSLZ	f
115	546	GRNCJ	f
115	589	NVUWR	f
115	591	8AO31	f
115	576	VQ6QD	f
115	530	5MFY4	f
115	558	0AAIK	f
115	536	VJVPV	f
115	577	L4ZXO	f
115	438	8P9AV	f
115	578	WYLB3	f
115	531	YHCMN	f
115	516	6ICXF	f
115	521	2JA8L	f
115	592	6KADM	f
115	545	QRZY3	f
115	537	HR1TX	f
115	573	6TTFC	f
115	580	NADNA	f
115	595	YHSWR	f
115	532	5RTGI	f
115	633	CXLOG	f
115	557	6WUTU	f
116	550	WTSYO	f
116	549	FXODR	f
116	498	O68RI	f
116	554	WYWCM	f
116	497	6EYCT	f
116	527	DFSJX	f
116	548	ZABPO	f
116	556	DUL7N	f
117	644	OGBLQ	f
117	645	UVZY1	f
117	646	AWVZI	f
117	650	AN99O	f
117	651	KFROL	f
117	652	TRIAL	f
118	582	ROET3	f
118	583	O5YWJ	f
118	584	PL7BO	f
118	585	EKS7N	f
118	586	O3ZFR	f
118	653	GALFI	f
118	654	SD4CT	f
118	655	TMEY0	f
118	598	SQMKE	f
118	599	ZL8PN	f
118	600	MY2JM	f
118	601	MR7SR	f
118	597	XSJ1R	f
118	656	INGRI	f
118	657	X2ZAY	f
118	658	QRFDD	f
118	659	VP4NM	f
118	660	L4NDG	f
118	604	ZPXIZ	f
118	605	J8F29	f
118	606	VYV80	f
118	607	X3CNZ	f
118	603	JURIK	f
118	661	K9TPF	f
118	662	OSGEG	f
118	663	Y5RDJ	f
118	664	73UOD	f
118	665	FYQC4	f
118	666	KPPCT	f
118	667	YLAXW	f
118	668	BWBMP	f
118	610	QBOMC	f
118	611	GA7PW	f
118	613	JFULZ	f
118	614	S8XVI	f
118	615	DRGTT	f
118	616	JSPNJ	f
118	617	II2QI	f
118	669	INEZH	f
118	670	9FAGL	f
118	671	46RKZ	f
118	672	WRFVF	f
118	448	QEKM9	f
118	641	LJTTB	f
118	642	HFBCE	f
118	643	DMPTC	f
118	673	TFFAL	f
118	674	JPK7U	f
118	675	4RKIO	f
118	676	47UFU	f
118	677	HSZXP	f
118	678	GJ0HI	f
118	638	DTAC1	f
118	640	E4CTM	f
118	679	D7GWA	f
118	625	SEXQZ	f
118	626	IDDXR	f
118	627	FDU4P	f
118	628	CLKNW	f
118	553	DTC9P	f
118	682	VN4TR	f
118	683	KRPUR	f
118	684	5EMPW	f
118	685	UF9RS	f
118	686	TVDUS	f
118	631	G8UG5	f
118	632	ATLS8	f
118	630	PNK7L	f
118	687	7EG4S	f
118	688	MSQJT	f
118	689	Z3VIZ	f
118	635	YZ3U6	f
118	636	LWLIZ	f
118	690	ZMFSW	f
118	691	XOZCE	f
119	582	LBCFR	f
119	583	RUT0Z	f
119	584	6GQPB	f
119	585	XGNMO	f
119	586	XL1PT	f
119	653	NMUBR	f
119	654	E8BMC	f
119	655	PUDZS	f
119	598	AUFJW	f
119	599	QLMD5	f
119	600	OFFQ7	f
119	601	MDGIO	f
119	597	ZNFB6	f
119	656	2BH8A	f
119	657	VCERB	f
119	658	Q1NZR	f
119	659	LYC85	f
119	660	RALFD	f
119	604	0X0VO	f
119	605	EHLVV	f
119	606	O8MCN	f
119	607	FDSFT	f
119	603	RY65D	f
119	661	DFXOB	f
119	662	YVULR	f
119	663	0CLRR	f
119	664	NH4TP	f
119	665	I4G63	f
119	666	ODBHH	f
119	667	MKB7Y	f
119	668	CGE24	f
119	610	XYBUX	f
119	611	CVX7B	f
119	613	6YT4D	f
119	614	NRECF	f
119	615	OAB6Y	f
119	616	TTVTU	f
119	617	XFFEH	f
119	669	KGIJM	f
119	670	NIOH1	f
119	671	HJXOS	f
119	672	FQVB2	f
119	448	HKUYB	f
119	641	5DGIS	f
119	642	3GOFB	f
119	643	01CEF	f
119	673	GWYV4	f
119	674	CGGNR	f
119	675	TRQVB	f
119	676	ZGSML	f
119	677	FAQ9E	f
119	678	3EM0H	f
119	638	VQGZX	f
119	640	IMKAN	f
119	679	Q2SMZ	f
119	625	WTDQR	f
119	626	LI1Y9	f
119	627	KYQ4R	f
119	628	KDC81	f
119	553	7GBGC	f
119	682	GV4EH	f
119	683	JYQCJ	f
119	684	T7VIU	f
119	685	STPU2	f
119	686	ZKYYX	f
119	631	JN3GK	f
119	632	NPYB0	f
152	1	H5AjN	f
154	2	uTYQP	f
154	3	0x4wD	f
154	4	vdAqf	f
154	5	STg1z	f
154	6	1DyvR	f
154	7	UueSJ	f
154	8	jlxsh	f
154	9	59X28	f
154	10	l5e2T	f
154	11	tFl4P	f
154	12	MtxuH	f
154	13	tHIzt	f
154	14	fjEDe	f
154	15	9o84B	f
154	16	eOxoC	f
154	17	NKuoq	f
154	18	NzKtr	f
154	19	3G59l	f
154	20	9Gdj8	f
154	21	Vjd5K	f
154	22	Pkzip	f
154	23	2g9E8	f
154	24	lhgAl	f
154	25	mHn6n	f
154	26	Evszn	f
154	27	4NGuf	f
154	28	Mbguj	f
154	29	gT11T	f
154	30	dZJQY	f
154	31	V5Y0d	f
154	32	vD3kP	f
154	33	x2hdw	f
154	34	sT7C9	f
154	35	OBqkD	f
154	36	t0J0I	f
154	37	0f73p	f
154	38	DhLxg	f
154	39	YtUzB	f
154	40	xQTPy	f
\.


--
-- Data for Name: exam_item; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.exam_item (exam_id, item_id, "order") FROM stdin;
43	1	1
43	2	2
43	3	3
43	723	4
\.


--
-- Data for Name: exam_session_logs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.exam_session_logs (id, attempt_id, session_key, tab_count, ip_address, country, city, isp, user_agent, notes, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: exams; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.exams (id, code, name, description, options, is_random, created_at, updated_at, is_mcq, is_interview, client_id, is_published, title, hash) FROM stdin;
5	1	TES SERVER MCQ	\N	\N	t	2017-07-11 23:01:04	2025-11-04 12:37:22	\N	f	3	f	\N	07jbzj8z
6	2	tes server ESSAY	\N	\N	t	2017-07-11 23:01:19	2025-11-04 12:37:22	\N	f	3	f	\N	QN53xe9y
7	3	Test coba	\N	\N	t	2017-07-31 07:39:48	2025-11-04 12:37:22	\N	f	3	f	\N	Yz5B3egR
8	BE12018	National Board Exam MCQ 4 January 2018	\N	\N	t	2017-12-07 18:28:07	2025-11-04 12:37:22	\N	f	3	f	\N	mJLp95Ek
9	BE12018-E	National Board Exam Short Essay 4 January 2018	\N	\N	t	2017-12-10 02:49:24	2025-11-04 12:37:22	\N	f	3	f	\N	bKeQqe1O
10	123	PEMILIHAN SOAL 2017	\N	\N	f	2017-12-10 09:16:53	2025-11-04 12:37:22	\N	f	3	f	\N	kO5og5KM
11	121	abcd	\N	\N	t	2017-12-26 09:44:03	2025-11-04 12:37:22	\N	f	3	f	\N	g95v7eVy
14	281217	TRY OUT ESSAY 281217	TRY OUT DESEMBER 2017	\N	t	2017-12-27 13:45:41	2025-11-04 12:37:22	\N	f	3	f	\N	X1LJMjkJ
15	BE18718MCQ	NATIONAL BOARD EXAM MCQ 18 JULI 2018	\N	\N	t	2018-07-01 09:53:38	2025-11-04 12:37:22	\N	f	3	f	\N	zQLyweVM
16	11718	TRY OUT ESSAY 11 JULI 2018	\N	\N	t	2018-07-09 08:04:08	2025-11-04 12:37:22	\N	f	3	f	\N	BVena5p6
17	BE18718ESSAY	NATIONAL BOARD EXAM ESSAY 18 JULI 2018	\N	\N	t	2018-07-12 21:55:00	2025-11-04 12:37:22	\N	f	3	f	\N	Wvjrp51M
18	121	TES SERVER ESSAY 14072018	\N	\N	t	2018-07-14 07:51:42	2025-11-04 12:37:22	\N	f	3	f	\N	BWjabLmy
19	212	TES SERVER MCQ 14072018	\N	\N	t	2018-07-14 07:52:04	2025-11-04 12:37:22	\N	f	3	f	\N	oajDxjqD
20	21-1-2018	21-1-2018	Ujian Institusi UNAIR	\N	t	2018-11-20 04:57:32	2025-11-04 12:37:22	\N	f	3	f	\N	pkLdKjxb
21	21-1-2018 -OSCE	21-1-2018 -OSCE	Ujian Institusi UNAIR OSCE	\N	f	2018-11-20 05:50:49	2025-11-04 12:37:22	\N	f	3	f	\N	vpjNVjzd
22	BE191218-OSCE	BE191218-OSCE	National Orthopaedic Board Examination Part-1 (OSCE) Surabaya-Jakarta	\N	t	2018-12-02 09:58:18	2025-11-04 12:37:22	\N	f	3	f	\N	ZNe0g5bY
24	111218	TRY OUT ESSAY 11-12-2018	\N	\N	t	2018-12-04 17:49:57	2025-11-04 12:37:22	\N	f	3	f	\N	k6LlBeK8
12	11718	TRY OUT MCQ 11 JULI 2018	\N	\N	t	2017-12-27 13:01:36	2025-11-04 12:42:27	\N	f	3	f	\N	vY5q2eBO
43	TEST25	TEST25	\N	\N	f	2025-11-04 14:02:15	2025-11-04 21:06:14	t	f	3	f	\N	KO560jEy
13	281217	Trial MCQ 281217	try out MCQ 28 December 2017	\N	t	2017-12-27 13:24:23	2025-11-04 21:06:14	\N	f	3	f	\N	2yjzZjQN
44	osce test	osce test	\N	\N	t	2025-11-04 21:21:40	2025-11-04 21:21:40	f	f	3	f	\N	lDj7yN50
23	BE191218-MCQ	BE191218-MCQ	National Orthopaedic Board Examination Part-1 (MCQ) Surabaya-Jakarta	\N	t	2018-12-02 13:49:25	2025-11-04 12:37:22	\N	f	3	f	\N	BvewJ5mE
25	11-12-2018	TRY OUT MCQ 11-12-2018	\N	\N	t	2018-12-04 17:51:03	2025-11-04 12:37:22	\N	f	3	f	\N	pRjVnjYw
26	BE29519-MCQ	National Board Examination MCQ - 29-05-2019	National Board Examination MCQ - 29-05-2019	\N	t	2019-05-21 20:39:27	2025-11-04 12:37:22	\N	f	3	f	\N	laj1rL3V
27	BE29519-OSCE	National Board Examination OSCE - 29-05-2019	National Board Examination OSCE - 29-05-2019	\N	t	2019-05-26 13:59:24	2025-11-04 12:37:22	\N	f	3	f	\N	XgLY1Lrk
32	MCQ 080520	Try Out CBT 080520 - MCQ	Try Out CBT 8 Mei 2020 - MCQ 50 soal - khusus kandidat	\N	t	2020-05-07 19:57:03	2025-11-04 12:37:22	\N	f	3	f	\N	PojOPLrB
33	BE12520-OSCE	National Board Examination OSCE - 12-05-2020	National Board Examination OSCE - 12-05-2020 - 25 soal/ 150 menit	\N	t	2020-05-11 00:31:32	2025-11-04 12:37:22	\N	f	3	f	\N	Zle91eRB
34	BE12520-MCQ	National Board Examination MCQ- 12-05-2020	National Board Examination MCQ- 12-05-2020 - 100 soal/ 120 minute	\N	t	2020-05-11 00:32:30	2025-11-04 12:37:22	\N	f	3	f	\N	Jde89j6b
37	BE 270521 - MCQ	NATIONAL BOARD EXAMINATION - MCQ - 27-05-2021	\N	\N	t	2021-05-25 21:54:32	2025-11-04 12:37:22	\N	f	3	f	\N	z8jZnjpD
38	BE 270521 - OSCE	NATIONAL BOARD EXAMINATION - OSCE - 27-05-2021	\N	\N	t	2021-05-25 22:04:51	2025-11-04 12:37:22	\N	f	3	f	\N	d0jmbLvA
28	BE131119-OSCE	NATIONAL BOARD EXAMINATION OSCE - 13 NOVEMBER 2019	NATIONAL BOARD EXAMINATION OSCE - 13 NOVEMBER 2019\nJAKARTA - SURABAYA	\N	f	2019-11-05 10:09:09	2025-11-04 21:06:14	\N	f	3	f	\N	38L495Y2
36	BE 181120 - MCQ	NATIONAL BOARD EXAMINATION - MCQ - 18 NOV 2020	National Board Examination of Indonesian Orthopedic and Traumatology College - Wednesday - Nov 18th, 2020	\N	t	2020-11-14 21:55:11	2025-11-04 21:06:14	\N	f	3	f	\N	o85Eq50a
35	BE 181120 - OSCE	NATIONAL BOARD EXAMINATION - OSCE -  18 NOV 2020	National Board Examination of Indonesian Orthopaedic and Traumatology College - Wednesday - Nov 18th, 2020	\N	t	2020-11-14 21:53:58	2025-11-04 21:06:14	\N	f	3	f	\N	PM5Rdjbn
31	OSCE 080520	Trial CBT 080520 - OSCE	Trial CBT OSCE 8 Mei 2020 - 5 soal -khusus kandidat	\N	t	2020-05-07 19:55:27	2025-11-04 21:06:14	\N	f	3	f	\N	7G521Lv9
30	IHKS-03-04-2020	IHKS Fellowship Board  Examination 03-04-2020	Ujian  MCQ Fellowship IHKS # April 2020	\N	t	2020-04-01 07:35:38	2025-11-04 21:06:14	\N	f	3	f	\N	8n5gMjX3
41	BE 240522 - MCQ	NATIONAL BOARD EXAMINATION - MCQ - 24-05-2022	NATIONAL BOARD EXAMINATION - MCQ - 24-05-2022 - peserta 50 kandidat	\N	t	2022-05-21 11:47:06	2025-11-04 21:06:14	\N	f	3	f	\N	dpLKrj28
42	BE 240522 - OSCE	NATIONAL BOARD EXAMINATION - OSCE - 24-05-2022	NATIONAL BOARD EXAMINATION - OSCE - 24-05-2022 - 50 peserta	\N	t	2022-05-21 11:47:38	2025-11-04 21:06:14	\N	f	3	f	\N	l75WnLXw
40	TRY OUT 180522 - MCQ	TRY OUT 180522 - MCQ	\N	\N	t	2022-05-17 15:52:52	2025-11-04 21:06:14	\N	f	3	f	\N	9GjGreM8
39	TRY OUT 180522 - OSCE	TRY OUT 180522 - OSCE	\N	\N	f	2022-05-17 15:49:10	2025-11-04 21:06:14	\N	f	3	f	\N	yOjPxj0W
29	BE131119 - MCQ`	NATIONAL BOARD EXAMINATION MCQ 13 NOVEMBER 2019	NATIONAL BOARD EXAMINATION MCQ 13 NOVEMBER 2019\nJAKARTA - SURABAYA	\N	f	2019-11-05 10:09:49	2025-11-04 21:06:14	\N	f	3	f	\N	XV5X6jQ7
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: group_taker; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.group_taker (taker_id, group_id, taker_code) FROM stdin;
1	1	001
2	2	BE 051125 - 01
3	2	BE 051125 - 02
4	2	BE 051125 - 03
5	2	BE 051125 - 04
6	2	BE 051125 - 05
7	2	BE 051125 - 06
8	2	BE 051125 - 07
9	2	BE 051125 - 08
10	2	BE 051125 - 09
11	2	BE 051125 - 10
12	2	BE 051125 - 11
13	2	BE 051125 - 12
14	2	BE 051125 - 13
15	2	BE 051125 - 14
16	2	BE 051125 - 15
17	2	BE 051125 - 16
18	2	BE 051125 - 17
19	2	BE 051125 - 18
20	2	BE 051125 - 19
21	2	BE 051125 - 20
22	2	BE 051125 - 21
23	2	BE 051125 - 22
24	2	BE 051125 - 23
25	2	BE 051125 - 24
26	2	BE 051125 - 25
27	2	BE 051125 - 26
28	2	BE 051125 - 27
29	2	BE 051125 - 28
30	2	BE 051125 - 29
31	2	BE 051125 - 30
32	2	BE 051125 - 31
33	2	BE 051125 - 32
34	2	BE 051125 - 33
35	2	BE 051125 - 34
36	2	BE 051125 - 35
37	2	BE 051125 - 36
38	2	BE 051125 - 37
39	2	BE 051125 - 38
40	2	BE 051125 - 39
\.


--
-- Data for Name: groups; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.groups (id, name, description, code, last_taker_code, created_at, updated_at, closed_at, client_id, hash) FROM stdin;
39	KANDIDAT UJIAN BOARD JAKARTA 18 JULI 2018	\N	\N	1	2018-07-12 09:35:53	2025-11-04 12:37:22	\N	3	eyGeLN8p
40	KANDIDAT UJIAN BOARD SURABAYA 18 JULI 2018	\N	\N	1	2018-07-12 09:36:02	2025-11-04 12:37:23	\N	3	mON3oN5l
41	Test Server	For Testing Server Only	\N	1	2018-07-14 10:17:00	2025-11-04 12:37:23	\N	3	d9zp3GbY
44	21-11-2018	Ujian Institusi UNAIR	\N	1	2018-11-20 06:19:31	2025-11-04 12:37:23	\N	3	qdGdEqzR
45	test 20-11-2018	\N	\N	1	2018-11-20 07:04:14	2025-11-04 12:37:23	\N	3	9vNJggGl
46	TRIAL CBT 11-12-2018	TRIAL CBT 11-12-2018	\N	1	2018-12-10 21:52:50	2025-11-04 12:37:23	\N	3	5bzOP5Mv
47	TRIAL CBT 11-12-2018 SENDIRI	\N	\N	1	2018-12-11 08:49:19	2025-11-04 12:37:23	\N	3	98G9v0z1
48	TRIAL CBT 11-12-2018 SHORT ESSAY UNUD	\N	\N	1	2018-12-11 08:57:52	2025-11-04 12:37:23	\N	3	reG8BVMB
49	TRIAL CBT 11-12-2018 SHORT ESSAY UNHAS	\N	\N	1	2018-12-11 08:59:33	2025-11-04 12:37:23	\N	3	lEzxwEGD
50	KANDIDAT UJIAN BOARD SURABAYA 19 DES 2018	Candidates of national board examination part 1- Surabaya	\N	1	2018-12-16 00:11:14	2025-11-04 12:37:23	\N	3	meGjqeNw
51	KANDIDAT UJIAN BOARD JAKARTA 19 DES 2018	Candidates - National board examination part 1 - Jakarta	\N	1	2018-12-16 00:13:01	2025-11-04 12:37:23	\N	3	o6NDxKNw
52	Kandidat Trial CBT 21519	\N	\N	1	2019-05-21 00:01:08	2025-11-04 12:37:23	\N	3	e7GLPEGO
53	National Board Examination Candidate 29-05-2019-Surabaya	National Board Examination Candidate 29-05-201-Surabaya	\N	1	2019-05-27 20:15:04	2025-11-04 12:37:23	\N	3	a8MyQ1MR
55	National Board Examination Candidate 29-05-2019-Jakarta	National Board Examination Candidate 29-05-2019-Jakarta	\N	1	2019-05-27 20:20:01	2025-11-04 12:37:23	\N	3	5azZeWGA
56	Trial cbt 5/11/19 FKUI	Trial cbt 5/11/19 FKUI	\N	1	2019-11-05 05:40:06	2025-11-04 12:37:23	\N	3	KezQnOMm
57	Trial cbt 5/11/19 FK UNPAD	Trial cbt 5/11/19 FK UNPAD	\N	1	2019-11-05 05:50:06	2025-11-04 12:37:23	\N	3	dqzX92MQ
58	Trial cbt 5/11/19 FK UNAIR	Trial cbt 5/11/19 FK UNAIR	\N	1	2019-11-05 05:50:27	2025-11-04 12:37:23	\N	3	0wzgA4NV
59	Trial cbt 5/11/19 FK UNHAS	Trial cbt 5/11/19 FK UNHAS	\N	1	2019-11-05 05:50:59	2025-11-04 12:37:23	\N	3	VrGEj5zw
60	Trial cbt 5/11/19 FK UNS	Trial cbt 5/11/19 FK UNS	\N	1	2019-11-05 05:51:15	2025-11-04 12:37:23	\N	3	a3MV6mM8
61	Trial cbt 5/11/19 FK UGM	Trial cbt 5/11/19 FK UGM	\N	1	2019-11-05 05:51:39	2025-11-04 12:37:23	\N	3	14N60jz0
62	Trial cbt 5/11/19 FK UNUD	Trial cbt 5/11/19 FK UNUD	\N	1	2019-11-05 05:51:58	2025-11-04 12:37:23	\N	3	p0NYO9z5
63	Trial cbt 5/11/19 FK UB	Trial cbt 5/11/19 FK UB	\N	1	2019-11-05 05:52:15	2025-11-04 12:37:23	\N	3	4nGq1KGl
64	Trial cbt 5/11/19 FK USU	Trial cbt 5/11/19 FK USU	\N	1	2019-11-05 05:52:28	2025-11-04 12:37:23	\N	3	wxN7wBNW
65	UB aziz	\N	\N	1	2019-11-05 08:01:49	2025-11-04 12:37:23	\N	3	YENnqrMl
66	NATIONAL BOARD EXAMINATION 131119  SURABAYA	ORTHOPEDIC CANDIDATES  131119 LOCATED ON SURABAYA	\N	1	2019-11-12 15:01:18	2025-11-04 12:37:23	\N	3	byN12pGg
67	NATIONAL BOARD EXAMINATION 131119 JAKARTA	ORTHOPEDIC CANDIDATES 131119 LOCATED ON JAKARTA	\N	1	2019-11-12 15:10:16	2025-11-04 12:37:23	\N	3	1WzReOGY
68	NATIONAL BOARD EXAMINATION IHKS FELLOWSHIP 2020	National Board Examination Indonesian Hip and Knee Fellowship 3 April 2020	\N	1	2020-04-02 22:03:19	2025-11-04 12:37:23	\N	3	kEzAvYML
69	Try Out CBT 050520 -  WIB	Try out ujian CBT di wilayah indonesia barat	\N	1	2020-05-04 22:28:00	2025-11-04 12:37:23	\N	3	lxNryYM1
70	Try Out CBT 050520 - WITA	Try Out CBT di  WITA	\N	1	2020-05-04 22:28:43	2025-11-04 12:37:23	\N	3	K8N5O9z4
71	KANDIDAT CBT 12520 - WIB	KANDIDAT CBT 12520 - WIB (USU, UI, UNPAD, UNS, UGM, UNAIR, UB)	\N	1	2020-05-07 06:29:30	2025-11-04 12:37:23	\N	3	EWMW7BMa
72	KANDIDAT CBT 12520 - WITA	KANDIDAT CBT 12520 - WITA (UNUD, UNHAS)	\N	1	2020-05-07 06:52:23	2025-11-04 12:37:23	\N	3	xYGPV2Mw
73	UJIAN IHKS 16-10-2020	Ujian CBT IHKS 16 Oktober 2020	\N	1	2020-10-15 10:01:37	2025-11-04 12:37:23	\N	3	mwMmqkM7
74	PESERTA UJIAN TRYOUT CBT 101120	\N	\N	1	2020-11-09 12:57:37	2025-11-04 12:37:23	\N	3	v7GBm8N0
75	PESERTA UJIAN CBT 181120	\N	\N	1	2020-11-09 12:57:57	2025-11-04 12:37:23	\N	3	Z0GaQdGv
76	Yoppi	\N	\N	1	2020-12-15 18:47:56	2025-11-04 12:37:23	\N	3	VDG07kN1
77	TRY OUT CBT 050521	\N	\N	1	2021-05-05 04:09:50	2025-11-04 12:37:23	\N	3	KVGoW3Gx
78	NATIONAL BOARD EXAMINATION - 260521	\N	\N	1	2021-05-05 06:25:27	2025-11-04 12:37:23	\N	3	Xozk6mGV
79	CANDIDATE NATIONAL BOARD EXAM - 270521	\N	\N	1	2021-05-25 22:54:52	2025-11-04 12:37:23	\N	3	w9N40gN1
80	IHKS Juli 2021	Ujian CBT IHKS Juli 2021	\N	1	2021-07-01 06:46:57	2025-11-04 12:37:23	\N	3	7WMbkPNV
81	kandidat CBT Ujian Adaptasi SpOT 291021	Ujian Adaptasi SpOT 29 Okt 2021	\N	1	2021-10-26 22:08:12	2025-11-04 12:37:23	\N	3	kENvYmMr
82	Candidate CBT Examination - Adaptation Program -291021	Candidate CBT Examination - Adaptation Program -291021	\N	1	2021-10-28 12:53:12	2025-11-04 12:37:23	\N	3	R6zKpBMK
84	Adaptation Test - Orthopaedic and Traumatology - 2022	Test adaptasi OT - 4 April 2022	\N	1	2022-04-03 12:21:21	2025-11-04 12:37:23	\N	3	mON30oG5
85	TRY OUT CBT 180522	\N	\N	1	2022-05-17 14:29:47	2025-11-04 12:37:23	\N	3	d9zpB3Nb
86	BE 240522	Ujian CBT  25 Mei 2022	\N	1	2022-05-21 11:34:39	2025-11-04 12:37:23	\N	3	XQzwoQzR
87	ORTHOPAEDIC BOARD EXAMINATION - ADAPTATION PROGRAM	\N	\N	1	2022-09-11 02:47:31	2025-11-04 12:37:23	\N	3	jwz27gMn
1	TEST USER05	TEST USER05 SYSTEM	TEST USER05	2	2025-11-04 14:00:57	2025-11-04 14:00:57	\N	3	9vNJgGl5
2	BE 051125		BE 051125	1	2025-11-04 14:55:06	2025-11-04 18:53:34	\N	3	5bzO5NvE
\.


--
-- Data for Name: items; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.items (id, title, content, type, is_vignette, is_random, score, created_at, updated_at, client_id, hash) FROM stdin;
735	BE051125 - MCQ 12	\N	multiple-choice	f	f	0	2025-11-04 20:11:00	2025-11-04 20:11:00	3	\N
1	Test 01-0411	\N	multiple-choice	f	t	0	2025-11-04 12:57:22	2025-11-04 13:04:11	3	VxgOjk3W
121	BE12018-3	<p>Figures above are the radiographs of a 50-year-old man who has pain radiating into both legs since 6 months ago. His back pain worsened &nbsp;with bending forward and ambulation and muscle atrophy are noted at his lower leg. His neurologic examination reveals normal strength in ankle dorsiflexion, but &nbsp;3/5 in ankle plantar flexion.</p>\n\n<p>&nbsp;</p>	multiple-choice	t	f	0	2017-12-07 18:41:40	2018-11-20 05:23:16	3	7RkNdPKp
159	BE12018-41	<p>Six month&nbsp;before coming to the clinic, an 80-year-old male sustained an open fracture on his left tibia but refuse any medical treatment. Today the patient come again to the hospital with pus draining&nbsp;from the wound and inability to walk. After thorough evaluation, you diagnose this patient with chronic osteomyelitis and non-union of the left tibia due to neglected untreated open fracture Cierny stage 3 type B.</p>	multiple-choice	t	f	0	2017-12-08 05:32:33	2018-11-20 05:18:57	3	DxKJabgq
160	BE12018-42	<p>A 53-year-old male arrives in the trauma bay with a GCS 15 and hemodynamically stable following a high-speed motor vehicle crash. He is complaining of severe right hip pain and is unable to move his right lower extremity. You note that his leg is shortened, slightly flexed, internally rotated, and adducted. He has symmetric pulses, and he is neurologically intact throughout his extremity. He has a normal examination otherwise. As part of the standard Advanced Trauma Life Support algorithm, an an AP pelvis is obtained seen in figure below (Picture 1)</p>	multiple-choice	t	f	0	2017-12-08 22:11:18	2017-12-08 22:28:39	3	MlK1Q9KN
162	BE12018-44	<p>A 55-year-old man complains of right ankle pain and inability to bear weight after he fell down a hill while mowing his lawn. AP and lateral radiographs of the right ankle demonstrate a spiral fracture of the fibula at the level of the plafond.</p>	multiple-choice	t	f	0	2017-12-08 22:42:24	2018-11-20 05:18:47	3	nWkmVJg6
163	BE12018-45	<p>A 49-year-old female with stage IV breast cancer presents to your office with worsening left hip pain. A recent bone scan ordered by her oncologist identifies multiple lesions in the spine, right humeral shaft, left clavicle, right ilium, and left proximal femur. She recently underwent radiation to the spine, left proximal femur, and right humeral shaft.</p>	multiple-choice	t	f	0	2017-12-09 00:20:37	2018-07-09 23:47:57	3	vAKz9ngj
165	BE12018-47	<p>A 55-year-old man with a long history of type 2 diabetes presents with a swollen, erythematous, hot, and mildly tender right foot. The majority of his erythema is localized in his midfoot. He reports that he has had increased pain in his foot for the past week. He has been doing his regular foot care without any skin breakdown. In addition, he has had no fevers, chills, or elevated blood sugars. On physical examination his vital signs are stable. He has swelling in his right foot, but no visible ulcer. His pulse is strongly palpable, but he has decreased sensation bilaterally in a stocking distribution to the level of the mid tibia. In addition to concern for infection you wish to evaluate the patient for Charcot foot.</p>	multiple-choice	t	f	0	2017-12-09 00:34:54	2018-11-20 05:18:12	3	7RkNAPBp
182	BE12018-51	<p>A 37-year-old, male motorcyclist was struck by another vehicle at highway speeds. He was intubated in the field. On arrival to the trauma bay his GCS is 3T, heart rate is 130 beats per minute, blood pressure is 90/58 mm Hg, lactate is 8, and his base deficit is 6. He is found to have a grade IV splenic laceration, multiple rib fractures, a pelvic ring injury, a closed left femur fracture, a comminuted Gustilo&ndash;Anderson 3C tibia fracture, and a small intraparenchymal hemorrhage. He is stabilized in the trauma bay and taken directly to the operating room by the general surgery team for exploratory laparotomy.</p>	multiple-choice	t	f	0	2017-12-09 09:16:57	2018-11-20 05:16:05	3	wJkq2XgO
2	Test 02-0411	\N	multiple-choice	f	t	0	2025-11-04 13:04:54	2025-11-04 13:04:54	3	qzKoYkDo
736	BE051125 - MCQ 13	\N	multiple-choice	f	f	0	2025-11-04 20:12:00	2025-11-04 20:12:00	3	\N
737	BE051125 - MCQ 14	\N	multiple-choice	f	f	0	2025-11-04 20:13:00	2025-11-04 20:13:00	3	\N
3	Test 03-0411	\N	multiple-choice	f	t	0	2025-11-04 13:09:06	2025-11-04 13:11:59	3	YRgyGB68
738	BE051125 - MCQ 15	\N	multiple-choice	f	f	0	2025-11-04 20:14:00	2025-11-04 20:14:00	3	\N
739	BE051125 - MCQ 1	\N	multiple-choice	f	f	0	2025-11-04 20:00:00	2025-11-04 20:00:00	3	\N
740	BE051125 - MCQ 2	\N	multiple-choice	f	f	0	2025-11-04 20:01:00	2025-11-04 20:01:00	3	\N
741	BE051125 - MCQ 9	\N	multiple-choice	f	f	0	2025-11-04 20:08:00	2025-11-04 20:08:00	3	\N
742	BE051125 - MCQ 10	\N	multiple-choice	f	f	0	2025-11-04 20:09:00	2025-11-04 20:09:00	3	\N
743	BE051125 - MCQ 16	\N	multiple-choice	f	f	0	2025-11-04 20:15:00	2025-11-04 20:15:00	3	\N
744	BE051125 - MCQ 17	\N	multiple-choice	f	f	0	2025-11-04 20:16:00	2025-11-04 20:16:00	3	\N
745	BE051125 - MCQ 18	\N	multiple-choice	f	f	0	2025-11-04 20:17:00	2025-11-04 20:17:00	3	\N
746	BE051125 - MCQ 19	\N	multiple-choice	f	f	0	2025-11-04 20:18:00	2025-11-04 20:18:00	3	\N
747	BE051125 - MCQ 20	\N	multiple-choice	f	f	0	2025-11-04 20:19:00	2025-11-04 20:19:00	3	\N
748	BE051125 - MCQ 21	\N	multiple-choice	f	f	0	2025-11-04 20:20:00	2025-11-04 20:20:00	3	\N
749	BE051125 - MCQ 22	\N	multiple-choice	f	f	0	2025-11-04 20:21:00	2025-11-04 20:21:00	3	\N
750	BE051125 - MCQ 23	\N	multiple-choice	f	f	0	2025-11-04 20:22:00	2025-11-04 20:22:00	3	\N
751	BE051125 - MCQ 24	\N	multiple-choice	f	f	0	2025-11-04 20:23:00	2025-11-04 20:23:00	3	\N
752	BE051125 - MCQ 25	\N	multiple-choice	f	f	0	2025-11-04 20:24:00	2025-11-04 20:24:00	3	\N
753	BE051125 - MCQ 26	\N	multiple-choice	f	f	0	2025-11-04 20:25:00	2025-11-04 20:25:00	3	\N
754	BE051125 - MCQ 27	\N	multiple-choice	f	f	0	2025-11-04 20:26:00	2025-11-04 20:26:00	3	\N
755	BE051125 - MCQ 28	\N	multiple-choice	f	f	0	2025-11-04 20:27:00	2025-11-04 20:27:00	3	\N
756	BE051125 - MCQ 29	\N	multiple-choice	f	f	0	2025-11-04 20:28:00	2025-11-04 20:28:00	3	\N
757	BE051125 - MCQ 30	\N	multiple-choice	f	f	0	2025-11-04 20:29:00	2025-11-04 20:29:00	3	\N
758	BE051125 - MCQ 31	\N	multiple-choice	f	f	0	2025-11-04 20:30:00	2025-11-04 20:30:00	3	\N
759	BE051125 - MCQ 32	\N	multiple-choice	f	f	0	2025-11-04 20:31:00	2025-11-04 20:31:00	3	\N
760	BE051125 - MCQ 33	\N	multiple-choice	f	f	0	2025-11-04 20:32:00	2025-11-04 20:32:00	3	\N
761	BE051125 - MCQ 34	\N	multiple-choice	f	f	0	2025-11-04 20:33:00	2025-11-04 20:33:00	3	\N
762	BE051125 - MCQ 35	\N	multiple-choice	f	f	0	2025-11-04 20:34:00	2025-11-04 20:34:00	3	\N
763	BE051125 - MCQ 36	\N	multiple-choice	f	f	0	2025-11-04 20:35:00	2025-11-04 20:35:00	3	\N
764	BE051125 - MCQ 37	\N	multiple-choice	f	f	0	2025-11-04 20:36:00	2025-11-04 20:36:00	3	\N
765	BE051125 - MCQ 38	\N	multiple-choice	f	f	0	2025-11-04 20:37:00	2025-11-04 20:37:00	3	\N
766	BE051125 - MCQ 39	\N	multiple-choice	f	f	0	2025-11-04 20:38:00	2025-11-04 20:38:00	3	\N
767	BE051125 - MCQ 40	\N	multiple-choice	f	f	0	2025-11-04 20:39:00	2025-11-04 20:39:00	3	\N
768	BE051125 - MCQ 41	\N	multiple-choice	f	f	0	2025-11-04 20:40:00	2025-11-04 20:40:00	3	\N
769	BE051125 - MCQ 42	\N	multiple-choice	f	f	0	2025-11-04 20:41:00	2025-11-04 20:41:00	3	\N
770	BE051125 - MCQ 43	\N	multiple-choice	f	f	0	2025-11-04 20:42:00	2025-11-04 20:42:00	3	\N
771	BE051125 - MCQ 44	\N	multiple-choice	f	f	0	2025-11-04 20:43:00	2025-11-04 20:43:00	3	\N
772	BE051125 - MCQ 45	\N	multiple-choice	f	f	0	2025-11-04 20:44:00	2025-11-04 20:44:00	3	\N
773	BE051125 - MCQ 46	\N	multiple-choice	f	f	0	2025-11-04 20:45:00	2025-11-04 20:45:00	3	\N
774	BE051125 - MCQ 47	\N	multiple-choice	f	f	0	2025-11-04 20:46:00	2025-11-04 20:46:00	3	\N
775	BE051125 - MCQ 48	\N	multiple-choice	f	f	0	2025-11-04 20:47:00	2025-11-04 20:47:00	3	\N
776	BE051125 - MCQ 49	\N	multiple-choice	f	f	0	2025-11-04 20:48:00	2025-11-04 20:48:00	3	\N
777	BE051125 - MCQ 50	\N	multiple-choice	f	f	0	2025-11-04 20:49:00	2025-11-04 20:49:00	3	\N
778	BE051125 - MCQ 51	\N	multiple-choice	f	f	0	2025-11-04 20:50:00	2025-11-04 20:50:00	3	\N
779	BE051125 - MCQ 52	\N	multiple-choice	f	f	0	2025-11-04 20:51:00	2025-11-04 20:51:00	3	\N
780	BE051125 - MCQ 53	\N	multiple-choice	f	f	0	2025-11-04 20:52:00	2025-11-04 20:52:00	3	\N
781	BE051125 - MCQ 54	\N	multiple-choice	f	f	0	2025-11-04 20:53:00	2025-11-04 20:53:00	3	\N
782	BE051125 - MCQ 55	\N	multiple-choice	f	f	0	2025-11-04 20:54:00	2025-11-04 20:54:00	3	\N
783	BE051125 - MCQ 56	\N	multiple-choice	f	f	0	2025-11-04 20:55:00	2025-11-04 20:55:00	3	\N
784	BE051125 - MCQ 57	\N	multiple-choice	f	f	0	2025-11-04 20:56:00	2025-11-04 20:56:00	3	\N
785	BE051125 - MCQ 58	\N	multiple-choice	f	f	0	2025-11-04 20:57:00	2025-11-04 20:57:00	3	\N
786	BE051125 - MCQ 59	\N	multiple-choice	f	f	0	2025-11-04 20:58:00	2025-11-04 20:58:00	3	\N
787	BE051125 - MCQ 61	\N	multiple-choice	f	f	0	2025-11-04 21:00:00	2025-11-04 21:00:00	3	\N
788	BE051125 - MCQ 62	\N	multiple-choice	f	f	0	2025-11-04 21:01:00	2025-11-04 21:01:00	3	\N
789	BE051125 - MCQ 63	\N	multiple-choice	f	f	0	2025-11-04 21:02:00	2025-11-04 21:02:00	3	\N
790	BE051125 - MCQ 64	\N	multiple-choice	f	f	0	2025-11-04 21:03:00	2025-11-04 21:03:00	3	\N
791	BE051125 - MCQ 65	\N	multiple-choice	f	f	0	2025-11-04 21:04:00	2025-11-04 21:04:00	3	\N
792	BE051125 - MCQ 66	\N	multiple-choice	f	f	0	2025-11-04 21:05:00	2025-11-04 21:05:00	3	\N
793	BE051125 - MCQ 67	\N	multiple-choice	f	f	0	2025-11-04 21:06:00	2025-11-04 21:06:00	3	\N
794	BE051125 - MCQ 68	\N	multiple-choice	f	f	0	2025-11-04 21:07:00	2025-11-04 21:07:00	3	\N
795	BE051125 - MCQ 69	\N	multiple-choice	f	f	0	2025-11-04 21:08:00	2025-11-04 21:08:00	3	\N
796	BE051125 - MCQ 70	\N	multiple-choice	f	f	0	2025-11-04 21:09:00	2025-11-04 21:09:00	3	\N
797	BE051125 - MCQ 71	\N	multiple-choice	f	f	0	2025-11-04 21:10:00	2025-11-04 21:10:00	3	\N
798	BE051125 - MCQ 72	\N	multiple-choice	f	f	0	2025-11-04 21:11:00	2025-11-04 21:11:00	3	\N
799	BE051125 - MCQ 73	\N	multiple-choice	f	f	0	2025-11-04 21:12:00	2025-11-04 21:12:00	3	\N
800	BE051125 - MCQ 74	\N	multiple-choice	f	f	0	2025-11-04 21:13:00	2025-11-04 21:13:00	3	\N
801	BE051125 - MCQ 75	\N	multiple-choice	f	f	0	2025-11-04 21:14:00	2025-11-04 21:14:00	3	\N
802	BE051125 - MCQ 76	\N	multiple-choice	f	f	0	2025-11-04 21:15:00	2025-11-04 21:15:00	3	\N
803	BE051125 - MCQ 77	\N	multiple-choice	f	f	0	2025-11-04 21:16:00	2025-11-04 21:16:00	3	\N
804	BE051125 - MCQ 78	\N	multiple-choice	f	f	0	2025-11-04 21:17:00	2025-11-04 21:17:00	3	\N
805	BE051125 - MCQ 79	\N	multiple-choice	f	f	0	2025-11-04 21:18:00	2025-11-04 21:18:00	3	\N
806	BE051125 - MCQ 80	\N	multiple-choice	f	f	0	2025-11-04 21:19:00	2025-11-04 21:19:00	3	\N
807	BE051125 - MCQ 81	\N	multiple-choice	f	f	0	2025-11-04 21:20:00	2025-11-04 21:20:00	3	\N
808	BE051125 - MCQ 82	\N	multiple-choice	f	f	0	2025-11-04 21:21:00	2025-11-04 21:21:00	3	\N
809	BE051125 - MCQ 83	\N	multiple-choice	f	f	0	2025-11-04 21:22:00	2025-11-04 21:22:00	3	\N
810	BE051125 - MCQ 84	\N	multiple-choice	f	f	0	2025-11-04 21:23:00	2025-11-04 21:23:00	3	\N
811	BE051125 - MCQ 85	\N	multiple-choice	f	f	0	2025-11-04 21:24:00	2025-11-04 21:24:00	3	\N
812	BE051125 - MCQ 86	\N	multiple-choice	f	f	0	2025-11-04 21:25:00	2025-11-04 21:25:00	3	\N
813	BE051125 - MCQ 87	\N	multiple-choice	f	f	0	2025-11-04 21:26:00	2025-11-04 21:26:00	3	\N
814	BE051125 - MCQ 88	\N	multiple-choice	f	f	0	2025-11-04 21:27:00	2025-11-04 21:27:00	3	\N
815	BE051125 - MCQ 89	\N	multiple-choice	f	f	0	2025-11-04 21:28:00	2025-11-04 21:28:00	3	\N
816	BE051125 - MCQ 90	\N	multiple-choice	f	f	0	2025-11-04 21:29:00	2025-11-04 21:29:00	3	\N
817	BE051125 - MCQ 91	\N	multiple-choice	f	f	0	2025-11-04 21:30:00	2025-11-04 21:30:00	3	\N
818	BE051125 - MCQ 92	\N	multiple-choice	f	f	0	2025-11-04 21:31:00	2025-11-04 21:31:00	3	\N
819	BE051125 - MCQ 93	\N	multiple-choice	f	f	0	2025-11-04 21:32:00	2025-11-04 21:32:00	3	\N
820	BE051125 - MCQ 94	\N	multiple-choice	f	f	0	2025-11-04 21:33:00	2025-11-04 21:33:00	3	\N
821	BE051125 - MCQ 95	\N	multiple-choice	f	f	0	2025-11-04 21:34:00	2025-11-04 21:34:00	3	\N
822	BE051125 - MCQ 96	\N	multiple-choice	f	f	0	2025-11-04 21:35:00	2025-11-04 21:35:00	3	\N
823	BE051125 - MCQ 97	\N	multiple-choice	f	f	0	2025-11-04 21:36:00	2025-11-04 21:36:00	3	\N
824	BE051125 - MCQ 98	\N	multiple-choice	f	f	0	2025-11-04 21:37:00	2025-11-04 21:37:00	3	\N
825	BE051125 - MCQ 99	\N	multiple-choice	f	f	0	2025-11-04 21:38:00	2025-11-04 21:38:00	3	\N
826	BE051125 - MCQ 100	\N	multiple-choice	f	f	0	2025-11-04 21:39:00	2025-11-04 21:39:00	3	\N
723	Test 04-0411	\N	multiple-choice	f	t	0	2025-11-04 13:54:49	2025-11-04 14:00:22	3	XGB33GB6
724	BE051125 - MCQ 1 & 2	<p>A 25-year-old man came to the emergency room complaining of wounds on the middle finger and ring finger of his left hand due to being hit by glass 1 hour before being admitted to the hospital. he can flex his fingers except his middle finger. The sensory for middle finger: ulnar site is hiposthesia and&nbsp; normal at radial site. The sensory for ring finger is normal at radial and ulnar site. CRT at all fingers less than 2 seconds. </p>	multiple-choice	t	f	0	2025-11-04 20:00:00	2025-11-04 20:00:00	3	7pKAdGg3
725	BE051125 - MCQ 3	\N	multiple-choice	f	f	0	2025-11-04 20:02:00	2025-11-04 20:02:00	3	AdknOGgR
726	BE051125 - MCQ 4	\N	multiple-choice	f	f	0	2025-11-04 20:03:00	2025-11-04 20:03:00	3	QnB0X4BA
728	BE051125 - MCQ 5	\N	multiple-choice	f	f	0	2025-11-04 20:04:00	2025-11-04 20:04:00	3	j0gxEGKM
729	BE051125 - MCQ 6	\N	multiple-choice	f	f	0	2025-11-04 20:05:00	2025-11-04 20:05:00	3	7pkZWXg9
730	BE051125 - MCQ 7	\N	multiple-choice	f	f	0	2025-11-04 20:06:00	2025-11-04 20:06:00	3	j4B4XQBz
731	BE051125 - MCQ 8	\N	multiple-choice	f	f	0	2025-11-04 20:07:00	2025-11-04 20:07:00	3	0Qk8mqgo
732	BE051125 - MCQ 9 & 10	<p>A 75-year-old woman underwent dynamic hip screw (DHS) fixation for a right intertrochanteric fracture 6 weeks ago. She now complains of persistent thigh pain, difficulty weight-bearing, and limb shortening. Follow-up X-ray shows collapse of the fracture site with lag screw migration<strong> </strong>through the femoral head.</p>	multiple-choice	t	f	0	2025-11-04 20:08:00	2025-11-04 20:08:00	3	JMBbPzgp
733	BE051125 - MCQ 11	\N	multiple-choice	f	f	0	2025-11-04 20:10:00	2025-11-04 20:10:00	3	rxKX5xBm
4	Titanium	\N	multiple-choice	f	t	0	2017-07-09 21:50:36	2018-07-09 23:39:57	3	1OgdYKzP
6	Tendon considered an anisotropic	\N	multiple-choice	f	f	0	2017-07-09 22:16:42	2018-07-09 23:40:16	3	PNKGLBnz
7	Spinal shock	\N	multiple-choice	f	f	0	2017-07-09 22:21:07	2017-07-09 22:21:12	3	prK2NKnG
8	Bone morphogenic protein	\N	multiple-choice	f	t	0	2017-07-09 22:23:26	2017-12-27 23:47:34	3	qZglYK5j
9	Examination of an obese 3-year-old-girl	\N	multiple-choice	f	f	0	2017-07-09 22:25:18	2017-07-09 22:25:18	3	oqKpNk2p
10	Immobilization of human tendons	\N	multiple-choice	f	t	0	2017-07-09 22:27:17	2018-07-09 23:42:01	3	z8KwNgob
11	A 12,5 year-old-boy reports intermittent knee pain and limping	\N	multiple-choice	f	f	0	2017-07-09 22:36:26	2017-07-09 22:36:26	3	r8gEdkjJ
12	The figure above shows the radiograph of a 2-year-old child	\N	multiple-choice	f	f	0	2017-07-09 22:39:55	2017-07-09 22:39:55	3	53gD1KyY
13	A 6-year-old boy with acute hematogenous osteomyelitis	\N	multiple-choice	f	t	0	2017-07-09 23:01:03	2018-07-09 23:40:40	3	3ZBYOg0y
14	A nonambulatory verbal 6-year-old child	\N	multiple-choice	f	t	0	2017-07-09 23:02:43	2017-12-27 23:48:21	3	dVg6wKpP
15	A 37-year-old man pulled his hamstring	\N	multiple-choice	f	f	0	2017-07-09 23:06:39	2018-07-09 23:42:09	3	DxKJ2Kq1
734	BE051125 - MCQ 60	\N	multiple-choice	f	f	0	2025-11-04 20:59:00	2025-11-04 20:59:00	3	6yk58WBb
16	A 13-year-old boy has a painless "knot"	\N	multiple-choice	f	f	0	2017-07-09 23:10:50	2018-07-14 08:01:35	3	MlK1ZBNY
17	A 17-year-old girl who initially presented during childhood with multiple skeletal lesion	\N	multiple-choice	f	t	0	2017-07-09 23:14:48	2017-12-27 23:48:07	3	nJg7DKlm
18	the four most common soft-tissue sarcomas	\N	multiple-choice	f	t	0	2017-07-09 23:17:16	2017-12-27 23:46:57	3	nWkmbg6D
19	A 13-year-old boy has knee pain after sustaining a mild twisting injury	\N	multiple-choice	f	t	0	2017-07-09 23:18:56	2017-12-27 23:49:18	3	vAKzOBjP
20	A 64-year-old man has had increasing pain in the left hip for the past 6 months	\N	multiple-choice	f	f	0	2017-07-09 23:25:38	2017-07-09 23:25:38	3	J8KQ0gWv
21	Radiograph and MRI scan of a 22-year-old man	\N	multiple-choice	f	t	0	2017-07-09 23:39:10	2018-07-14 08:01:45	3	7RkNqBp6
22	Local recurrence of the lesion	\N	multiple-choice	f	t	0	2017-07-09 23:42:41	2017-12-27 23:49:45	3	68gRdBYl
23	A 33-year-old woman reports a mass	\N	multiple-choice	f	f	0	2017-07-09 23:46:17	2017-07-09 23:46:17	3	XGB3ag6O
24	A 15-year-old girl had a painful mass	\N	multiple-choice	f	f	0	2017-07-09 23:51:00	2018-07-09 23:42:48	3	7pKA3k3l
25	In patients with displaced radial neck fractures	\N	multiple-choice	f	f	0	2017-07-09 23:53:25	2018-07-09 23:42:40	3	AdknqBRE
26	When harvesting an iliac crest bone graft	\N	multiple-choice	f	f	0	2017-07-09 23:54:50	2017-07-09 23:54:50	3	QnB01kA6
27	A 36-year-old woman sustained a tarsometatarsal j	\N	multiple-choice	f	f	0	2017-07-09 23:56:04	2017-07-09 23:56:04	3	wdKv1g2a
28	What is the most appropriate indication for replantation	\N	multiple-choice	f	t	0	2017-07-09 23:57:46	2017-12-27 23:49:00	3	j0gxaKMR
29	A 46-year-old man fell 20 feet and sustained the injury	\N	multiple-choice	f	t	0	2017-07-09 23:59:18	2017-12-27 23:56:01	3	7pkZLK93
30	A 20-year-old man sustained	\N	multiple-choice	f	t	0	2017-07-10 00:01:04	2018-07-09 23:42:55	3	j4B4WKzX
31	An active 49-year-old woman who sustained	\N	multiple-choice	f	f	0	2017-07-10 00:02:23	2017-07-10 00:02:23	3	0Qk8NKoq
32	Examination of a 25-year-old man who was injured	\N	multiple-choice	f	t	0	2017-07-10 00:03:48	2017-12-27 23:49:32	3	JMBbmKpV
33	Figures above shown the initial radiograph of an 18-year-old man	\N	multiple-choice	f	f	0	2017-07-10 00:05:24	2017-12-27 23:55:48	3	rxKXPKmR
34	Which is the following is an advantage of un-reamed nailing	\N	multiple-choice	f	t	0	2017-07-10 00:08:42	2017-12-27 23:45:39	3	6yk51kb7
35	What is the major difference in outcome	\N	multiple-choice	f	t	0	2017-07-10 00:10:05	2017-12-27 23:50:45	3	4Og95g6W
36	The figure above shows the radiograph pf an elderly man	\N	multiple-choice	f	f	0	2017-07-10 00:11:52	2017-07-10 00:11:52	3	x5gLjB1p
323	OBS-71	\N	multiple-choice	f	t	0	2018-05-20 23:09:03	2018-05-20 23:09:03	3	XGB3XLk6
39	A 35-year-old man is brought to the emergency department	\N	multiple-choice	f	t	0	2017-07-10 00:37:33	2017-12-27 23:44:39	3	RxBW6Krd
40	A 42-year-old woman sustained	\N	multiple-choice	f	f	0	2017-07-10 00:39:37	2017-07-10 00:39:37	3	3oKM8g6e
41	A 25-year-old man is brought to the emergency department	\N	multiple-choice	f	f	0	2017-07-10 00:41:38	2017-07-10 00:41:38	3	xDkVABXN
42	A 9-year-old child sustains a proximal tibial physeal fracture	\N	multiple-choice	f	t	0	2017-07-10 00:43:08	2017-12-27 23:45:10	3	n0BP7KLa
43	An 8-year-old girl has treated for a Salter-harris	\N	multiple-choice	f	f	0	2017-07-10 00:44:41	2017-07-10 00:44:41	3	lAKjMg9m
45	In obstetric brachial plexus palsy	\N	multiple-choice	f	f	0	2017-07-10 00:49:33	2017-07-10 00:49:33	3	VxgOjjk3
46	Split posterior tibial tendon	\N	multiple-choice	f	f	0	2017-07-10 00:51:00	2017-12-27 23:44:09	3	qzKoyYkD
47	A 72-year-old male is complaining of dull pain	\N	multiple-choice	f	f	0	2017-07-10 00:52:23	2017-07-10 00:52:23	3	YRgyQGg6
48	A 3-year-old boy was brought by his mother with the concern of tilting neck	\N	multiple-choice	f	t	0	2017-07-10 00:53:50	2017-12-27 23:43:46	3	1OgdpYgz
49	An overweight  74-year-old lady is complaining about low back pain	\N	multiple-choice	f	f	0	2017-07-10 00:55:10	2018-07-09 23:44:22	3	pwBew1gQ
50	A 30-year-old man reports pain and weakness in his right arm	\N	multiple-choice	f	f	0	2017-07-10 00:57:29	2017-07-10 00:57:29	3	PNKGRLgn
53	A 26-year-old man reports a 2-week history	\N	multiple-choice	f	f	0	2017-07-10 01:00:19	2018-07-09 23:44:31	3	oqKpdNK2
54	An 18-year-old man sustained a knife injury to his mid-back	\N	multiple-choice	f	f	0	2017-07-10 01:01:48	2017-07-10 01:01:48	3	z8KwdNBo
55	In thoracolumbar fracture	\N	multiple-choice	f	f	0	2017-07-10 01:03:20	2017-12-27 23:42:53	3	r8gEQdgj
56	Flexion injury in thoracolumbar	\N	multiple-choice	f	f	0	2017-07-10 01:04:43	2017-12-27 23:42:32	3	53gDv1By
57	Vertebral compression fracture	\N	multiple-choice	f	f	0	2017-07-10 01:05:56	2017-07-10 01:05:56	3	3ZBYaOB0
58	Spondyloarthropathies	\N	multiple-choice	f	f	0	2017-07-10 01:07:26	2017-07-10 01:07:26	3	dVg65wkp
59	ankylosing spondylitis	\N	multiple-choice	f	f	0	2017-07-10 01:13:07	2018-07-09 23:44:38	3	DxKJr2Kq
60	discectomy in disc herniation	\N	multiple-choice	f	f	0	2017-07-10 01:16:03	2017-07-10 01:16:03	3	MlK1oZkN
62	Neurogenic claudication lumbar spinal stenosis	\N	multiple-choice	f	t	0	2017-07-10 01:20:16	2017-12-27 23:42:15	3	nWkmxbB6
63	the herniated disc	\N	multiple-choice	f	f	0	2017-07-10 01:22:57	2017-07-10 01:22:57	3	vAKzpOkj
64	A healthy, active 72-year-old man tripped and fell	\N	multiple-choice	f	t	0	2017-07-10 01:25:29	2017-12-27 23:41:49	3	J8KQ20BW
65	A 70-year-old woman who underwent total knee replacement	\N	multiple-choice	f	f	0	2017-07-10 01:28:01	2017-07-10 01:28:01	3	7RkN5qBp
66	A 67-year-old man who underwent an uncomplicated hip arthroplasty	\N	multiple-choice	f	f	0	2017-07-10 01:29:37	2017-07-10 01:29:37	3	68gRNdgY
67	radiographs of a 25-year-old	\N	multiple-choice	f	f	0	2017-07-10 01:32:54	2017-07-10 01:32:54	3	XGB3qag6
68	An orthopaedic surgeon	\N	multiple-choice	f	f	0	2017-07-10 01:34:40	2018-07-09 23:44:46	3	7pKAR3B3
69	A 59-year-old active woman underwent	\N	multiple-choice	f	f	0	2017-07-10 01:36:47	2018-07-14 07:59:39	3	Adkn0qKR
70	after total hip arthroplasty	\N	multiple-choice	f	f	0	2017-07-10 01:39:41	2017-07-10 01:39:41	3	QnB0V1gA
72	Which of the following has been shown to increase the rate of failure	\N	multiple-choice	f	f	0	2017-07-10 01:48:41	2017-07-10 01:48:41	3	j0gxVaBM
73	A 40-year-old female sustains the injury seen in Figure Above	\N	multiple-choice	f	f	0	2017-07-10 01:50:05	2018-07-14 07:58:47	3	7pkZ8LK9
74	A 34-year-old male presents with the right posterior	\N	multiple-choice	f	f	0	2017-07-10 01:52:17	2017-07-10 01:52:17	3	j4B4vWgz
75	Which is the following nerve roots is at risk during anterior	\N	multiple-choice	f	f	0	2017-07-10 01:56:00	2017-07-10 01:56:00	3	0Qk8VNKo
77	The statements bellow are right for fracture	\N	multiple-choice	f	f	0	2017-07-10 01:58:29	2017-07-10 01:58:29	3	rxKXqPBm
78	A 3-year-old boy has a rigid 40-degree	\N	multiple-choice	f	f	0	2017-07-10 01:59:47	2017-07-10 01:59:47	3	6yk5v1gb
79	In a patient with vertebral tuberculosis	\N	multiple-choice	f	f	0	2017-07-10 02:01:02	2017-07-10 02:01:02	3	4Og9M5g6
80	A patient is seen shortly after birth	\N	multiple-choice	f	f	0	2017-07-10 02:02:16	2017-07-10 02:02:16	3	x5gLAjK1
82	An active, 19-year-old gymnast	<p>An active, 19-year-old gymnast complains of ulnar-sided wrist pain. &nbsp;She has already obtained an MRI scan which reveals ECU tendinitis.</p>	multiple-choice	t	f	0	2017-07-10 02:14:29	2017-07-10 02:21:10	3	wJkqeYkO
83	A 7-year-old boy arrives at the emergency	<p>A 7-year-old boy arrives at the emergency department with forearm pain. Today he was picking up his backpack when he felt a pop in his forearm that resulted in the current injury. &nbsp;His history is significant for 5 other fractures treated nonsurgically. His mother states that she had 14 fractures during childhood but is healthy now. Both the boy and his mother have blue sclera. &nbsp;Figures above are the radiographs of his injured forearm. &nbsp;</p>	multiple-choice	t	f	0	2017-07-10 02:21:32	2018-07-14 07:58:58	3	RxBW06kr
84	A 56-year -old homemaker fell down the steps	<p>A 56-year -old homemaker fell down the steps of her basement injuring her left ring finger. She was seen at an outside facility with significant deformity of the ring finger. There were no open wounds. There were severe pain and limited motion. Radiographs are shown in Figures above.</p>	multiple-choice	t	f	0	2017-07-10 02:32:12	2018-07-14 07:58:35	3	3oKMa8K6
112	Adult recon 2	<p>A 35-year-old-female had motorcycle accident. Her knee was hit by another motorcycle from front.</p>	essay	t	f	0	2017-07-10 14:43:05	2017-07-10 14:53:34	3	53gDxWgy
324	OBS-72	\N	multiple-choice	f	t	0	2018-05-20 23:12:15	2018-05-20 23:12:15	3	7pKAMdK3
325	TUMOR-1	\N	multiple-choice	f	t	0	2018-05-20 23:14:10	2018-11-20 05:22:13	3	AdkndbKR
86	A 25-year-old worker reporting back pain and weakness	<p>A 25-year-old worker reporting back pain and weakness of both legs after felt down from top of the roof. The physical examination finding stable hemodynamic and he still able to move both lower leg with motoric power 3/5. AP X-ray view is shown in figure above.</p>	multiple-choice	t	f	0	2017-07-10 02:41:39	2017-12-27 23:52:57	3	n0BPb7gL
87	A 66 years old male , unable to walk for the	<p>A 66 years old male , unable to walk for the past six months and weakness of both hand, no history of trauma . Increasing of knee jerk and positive babinsky test are noted. Hoffman Tromner test are positive . The cervical x ray shown above</p>	multiple-choice	t	f	0	2017-07-10 02:44:59	2017-07-10 02:47:12	3	lAKjVMK9
88	Here is the radiograph of a 9-year-old African-American boy	<p>Here is the radiograph of a 9-year-old African-American boy with left-sided groin and knee pain.</p>\n\n<p>His body mass index is 32, and he has had these symptoms for 10 days.</p>	multiple-choice	t	f	0	2017-07-10 02:48:20	2018-07-09 23:52:41	3	DjBr0lg2
89	A 40-year-old man who arrives at the emergency department with a 4-day	<p>A 40-year-old man who arrives at the emergency department with a 4-day history of fevers and severe back pain without radiation. He is normotensive at presentation with a heart rate of 86 beats per minute. Upon examination he is neurologically intact with normal sensory and motor function. He has a history of alcohol and cocaine abuse. His white blood cell (WBC) count is 12000 (reference range [rr], 4500-11000/ &mu;L) and his C-reactive protein (CRP) level is 100 mg/L (rr, 0.08-3.1 mg/L).</p>	multiple-choice	t	f	0	2017-07-10 02:52:58	2017-12-27 23:54:05	3	VxgOejB3
90	Spine 5	<p>A 57-year-old woman who has had 3 months of back pain that radiates into her left anterolateral thigh, anterior shin, and medial ankle. Her pain has persisted after participating in physical therapy and receiving medications and an epidural injection. She has a positive straight-leg raise result and weakness in the left ankle dorsoflexion</p>	essay	t	f	0	2017-07-10 03:19:59	2017-07-10 15:06:56	3	qzKoYYkD
91	Spine 4	<p>A 47-year-old man has with chief complain back pain for 6 months. &nbsp;Night pain are noted and pain in change of position. Motoric power &nbsp;of lower extremities are 4/5. &nbsp;MRI of thoracal spine is showed below.</p>	essay	t	f	0	2017-07-10 03:25:15	2017-07-10 15:06:40	3	YRgyZGK6
92	Spine 3	<p>A &nbsp;65 -year-old woman &nbsp;has main complain back pain and &nbsp;radiating pain &nbsp;into both legs. Her pain improves with bending forward or lying flat and worsens with ambulation. Her neurologic examination reveals 4/5 strength in ankle dorsiflexion, but otherwise is unremarkable. She has attempted nonsurgical care including physical therapy and medications for 6 months.</p>	essay	t	f	0	2017-07-10 03:31:11	2018-07-09 23:33:44	3	1OgdzYBz
93	Spine 2	<p>A sagittal CT scan of a 77-year-old woman who has been experiencing back pain for about 1 month. No history of trauma, No muscle weakness and sensory disturbances.</p>	essay	t	f	0	2017-07-10 03:32:48	2017-07-10 15:06:13	3	pwBeZ1kQ
94	Spine 1	<p>21-year-old female complain about severe back pain after her car hit a tree. There is no neurologic deficit. The sagittal CT of lumbar spine is showed below</p>	essay	t	f	0	2017-07-10 03:36:08	2017-07-10 15:05:26	3	PNKGALkn
96	Oncology 4	\N	essay	f	f	0	2017-07-10 12:15:38	2018-12-04 18:36:04	3	qZglbYB5
97	Oncology 3	\N	essay	f	f	0	2017-07-10 12:35:48	2018-12-04 18:36:15	3	oqKpxNK2
98	Oncology 2	\N	essay	f	f	0	2017-07-10 12:41:59	2018-12-04 18:36:25	3	z8KwQNko
99	Pediatric 2	<p>A 4 years old boy cannot move his left elbow after a fall onto his left &nbsp;hand while the elbow is flexed</p>	essay	t	f	0	2017-07-10 14:04:26	2017-07-10 15:04:15	3	r8gE6dKj
147	BE12018-29	\N	multiple-choice	f	t	0	2017-12-07 22:26:25	2017-12-07 22:26:25	3	YRgyGLB6
100	Pediatric 5	<p>A two year old baby came to your clinic with a deformities on both of his feet</p>	essay	t	f	0	2017-07-10 14:08:10	2017-07-10 14:59:03	3	DjBr01g2
101	Oncology 1	\N	essay	f	f	0	2017-07-10 14:09:45	2018-12-04 18:36:35	3	VxgOenB3
102	Pediatric 4	<p>An 8 year old boy came with a swollen wrist after a fall in a soccer play</p>	essay	t	f	0	2017-07-10 14:12:33	2018-07-09 23:32:33	3	qzKoYZkD
103	Pediatric 3	<p>A 4 years ol girl came to your clinic with a short-leg gait of the left lower extremity. History shows that she was born with breech presentation. There is no history of pain or fever. Physical examination reveals a short-leg gait with 3 cm of leg length discrepancy. The trendelenburg sign on the left hip is positive with limited abduction</p>	essay	t	f	0	2017-07-10 14:15:26	2017-07-10 14:56:34	3	YRgyZLK6
105	Pediatric1	<p>A 5 years old boy came with the chief complain tilted neck. The deformity was started since he was born. He had undergone sessions of stretching &nbsp;and physiotherapy however the compliance was low.</p>	essay	t	f	0	2017-07-10 14:20:16	2018-07-14 07:57:09	3	pwBeZ5kQ
106	Adult recon 5	<ul>\n\t<li>\n\t<p>A 35-year-old-male complaining pain on the right knee. Pain especially during up and down stairs and unstable knee while walking.</p>\n\t</li>\n\t<li>\n\t<p>He had history &nbsp;of traffic accident 1 years ago.</p>\n\t</li>\n</ul>	essay	t	f	0	2017-07-10 14:28:36	2018-07-14 07:58:18	3	PNKGAXkn
107	Hand 1	<p>44 years old right-handed female maid complaining pain at her radial sided wrist, she denies previous trauma</p>	essay	t	f	0	2017-07-10 14:33:49	2017-07-10 14:37:10	3	prK2yegn
108	Adult recon 4	<p>Female, 42 years old came complaining pain at medial side of her forefoot. History of daily high heels usage is admitted. The clinical pictures is as follows.<br />\n&nbsp;</p>	essay	t	f	0	2017-07-10 14:36:05	2017-07-10 14:54:37	3	qZglbpB5
109	Hand 2	<p>Male 25 years old right-handed male arrived at the emergency room after accidentally he cut his own finger when working.</p>	essay	t	f	0	2017-07-10 14:37:32	2017-07-10 14:41:41	3	oqKpxlK2
111	Hand 3	<p>45 years old right-handed male complain about deformity of his right index finger. He says that 9 months ago, his deformed finger is only on the distal joint that cannot extend after being hit by a ball when trying to catch it.</p>	essay	t	f	0	2017-07-10 14:42:27	2017-07-10 14:45:50	3	r8gE6YKj
315	OBS-63	\N	multiple-choice	f	t	0	2018-05-20 22:48:42	2018-05-20 22:48:42	3	DxKJ55gq
114	Hand 4	<p>35 years old right-handed male after healed fingertip injury</p>	essay	t	f	0	2017-07-10 14:46:16	2017-07-10 14:48:38	3	dVg62jgp
115	Hand 5	<p>33 years old right-handed male complaining pain at his hand after Blowing a fist to a robber</p>\n\n<div>&nbsp;</div>	essay	t	f	0	2017-07-10 14:49:13	2018-07-14 07:56:55	3	DxKJRbKq
116	Oncology 5	\N	essay	f	f	0	2017-07-11 21:49:47	2018-12-04 18:36:46	3	MlK189gN
119	BE12018-1	\N	multiple-choice	f	t	0	2017-12-07 18:12:07	2018-11-20 05:23:30	3	vAKz3nBj
120	BE12018-2	\N	multiple-choice	f	t	0	2017-12-07 18:36:46	2018-01-03 09:25:04	3	J8KQDOgW
122	BE12018-4	<p>A 22-year-old man has a cervical fracture-dislocation after being involved in a motor vehicle collision. Examination reveals normal sensation in his upper extremities and strength that is graded as 5/5 in his biceps, 4/5 in wrist extensors, 4/5 in triceps, and 0/5 in his hands and inferior extremities muscles. With preservation of pinprick sensation only at perianal region</p>	multiple-choice	t	f	0	2017-12-07 18:57:17	2018-07-09 23:37:23	3	68gRYEkY
124	BE12018-6	\N	multiple-choice	f	t	0	2017-12-07 19:08:44	2017-12-07 19:08:44	3	7pKAvDB3
125	BE12018-7	\N	multiple-choice	f	t	0	2017-12-07 19:15:44	2017-12-07 19:15:44	3	AdknxyKR
126	BE12018-8	<p>This is the MR image of a 43-year-old woman with a 3-week history of neck pain radiating into her left arm. She denies&nbsp;weakness, or problems with balance. Examination reveals her pain is reproduced with ipsilateral neck rotation.</p>	multiple-choice	t	f	0	2017-12-07 19:20:30	2017-12-10 09:42:27	3	QnB0OAKA
127	BE12018-9	<p>This is cervical spine x-ray of a 25-year-old man who has neck pain and tetraplegia after a motor vehicle collision.</p>	multiple-choice	t	f	0	2017-12-07 19:30:40	2017-12-07 19:48:16	3	wdKv5Gg2
128	BE12018-10	\N	multiple-choice	f	t	0	2017-12-07 19:49:07	2018-11-20 05:22:56	3	j0gxR5BM
129	BE12018-11	<p>This is an axial MR image of a 34-year-old woman who has had severe right leg pain for 3 months. The pain starts in her back and radiates into her posterior thigh, lateral shin, and dorsum of the foot. Examination reveals a positive right straight-leg raise result, weakness (4/5) in the right extensor hallucis longus, and numbness in her great toe.</p>	multiple-choice	t	f	0	2017-12-07 20:33:49	2018-11-20 05:23:05	3	7pkZ5xB9
130	BE12018-12	<p>These are CT scan and MR image of a 60-year-old woman who fell at home. She has severe back pain after trauma. Examination reveals severe thoracic kyphosis, but the motor and sensory functions are normal.</p>	multiple-choice	t	f	0	2017-12-07 20:45:03	2017-12-07 20:57:20	3	j4B4e8kz
131	BE12018-13	<p>This is the lateral radiograph of the lumbosacral junction and pelvis of a 67-year-old woman with a lumbar deformity who is being evaluated for surgery.</p>	multiple-choice	t	f	0	2017-12-07 20:59:08	2017-12-07 21:09:08	3	0Qk8M0go
132	BE12018-14	\N	multiple-choice	f	t	0	2017-12-07 21:12:13	2017-12-07 21:12:13	3	JMBbb2Bp
133	BE12018-15	\N	multiple-choice	f	t	0	2017-12-07 21:20:32	2018-07-09 23:38:22	3	rxKXXqKm
134	BE12018-16	\N	multiple-choice	f	t	0	2017-12-07 21:34:07	2017-12-07 21:35:45	3	6yk5A0kb
137	BE12018-19	\N	multiple-choice	f	t	0	2017-12-07 21:46:53	2017-12-07 21:46:53	3	VbBaGXgw
138	BE12018-20	\N	multiple-choice	f	t	0	2017-12-07 21:49:39	2019-11-08 09:30:17	3	wJkqVXgO
139	BE12018-21	\N	multiple-choice	f	t	0	2017-12-07 21:52:11	2017-12-07 21:52:11	3	RxBWzQKr
140	BE12018-22	\N	multiple-choice	f	t	0	2017-12-07 21:54:34	2017-12-07 21:54:34	3	3oKMRpB6
141	BE12018-23	\N	multiple-choice	f	t	0	2017-12-07 21:59:02	2017-12-07 21:59:02	3	xDkVW6gX
142	BE12018-24	\N	multiple-choice	f	t	0	2017-12-07 22:01:49	2017-12-07 22:01:49	3	n0BPZAgL
143	BE12018-25	\N	multiple-choice	f	t	0	2017-12-07 22:04:49	2017-12-07 22:04:49	3	lAKjElg9
144	BE12018-26	\N	multiple-choice	f	t	0	2017-12-07 22:14:26	2017-12-07 22:14:26	3	DjBr91K2
145	BE12018-27	\N	multiple-choice	f	t	0	2017-12-07 22:18:23	2017-12-07 22:18:23	3	VxgOQnB3
146	BE12018-28	\N	multiple-choice	f	t	0	2017-12-07 22:22:02	2017-12-07 22:22:02	3	qzKoxZBD
148	BE12018-30	\N	multiple-choice	f	t	0	2017-12-07 22:30:52	2017-12-07 22:30:52	3	1OgdM9Kz
149	BE12018-31	\N	multiple-choice	f	t	0	2017-12-08 04:26:58	2017-12-08 04:26:58	3	pwBe65kQ
150	BE12018-32	\N	multiple-choice	f	t	0	2017-12-08 04:36:44	2018-11-20 05:19:15	3	PNKGWXKn
151	BE12018-33	\N	multiple-choice	f	t	0	2017-12-08 04:41:05	2017-12-08 04:41:05	3	prK2jeKn
152	BE12018-34	\N	multiple-choice	f	t	0	2017-12-08 04:44:07	2017-12-08 04:44:07	3	qZglVpB5
154	BE12018-36	\N	multiple-choice	f	t	0	2017-12-08 04:52:33	2017-12-08 04:52:33	3	z8KwZLgo
155	BE12018-37	\N	multiple-choice	f	t	0	2017-12-08 04:55:52	2019-11-08 09:31:28	3	r8gEJYKj
156	BE12018-38	\N	multiple-choice	f	t	0	2017-12-08 04:58:20	2018-11-20 05:19:07	3	53gDLWgy
157	BE12018-39	\N	multiple-choice	f	t	0	2017-12-08 05:24:32	2019-11-08 09:33:25	3	3ZBYYqB0
158	BE12018-40	\N	multiple-choice	f	t	0	2017-12-08 05:27:41	2017-12-08 05:27:41	3	dVg66jgp
161	BE12018-43	<p>A 26-year-old man involved in a high-speed motor vehicle collision arrives to the emergency department with an obvious deformity of the right leg. Orthogonal radiographs of the right tibia/fibula are depicted in figures below. An initial assessment deems the patient to be stable with no evidence of associated intrathoracic or intra-abdominal injuries. The skin is intact, there are palpable pulses distally, soft compartments, and the patient is deemed suitable for operative treatment</p>	multiple-choice	t	f	0	2017-12-08 22:32:05	2017-12-08 22:40:53	3	nJg7rjkl
316	OBS-64	\N	multiple-choice	f	t	0	2018-05-20 22:50:33	2018-05-20 22:50:33	3	MlK1E3KN
317	OBS-65	\N	multiple-choice	f	t	0	2018-05-20 22:53:16	2018-05-20 22:53:16	3	nJg7MXBl
164	BE12018-46	<p>A 16-year-old female sprains her ankle playing Soccer&nbsp;and is brought to the emergency room. X-rays are negative for fracture, but an orthopaedic consult is placed to evaluate a suspicious lesion in the distal tibia. Prior to her acute ankle injury, she denies any pain in her lower leg. Her father died at a young age of colon cancer, and she is anxious about this finding being suggestive of malignancy. X-ray is shown in figure below.</p>	multiple-choice	t	f	0	2017-12-09 00:28:12	2018-11-20 05:18:39	3	J8KQQOKW
166	BE12018-60	\N	multiple-choice	f	t	0	2017-12-09 00:42:19	2018-07-09 23:45:51	3	68gR6EgY
167	BE12018-59	\N	multiple-choice	f	t	0	2017-12-09 00:44:41	2018-11-20 05:18:25	3	XGB3dxk6
168	BE12018-58	\N	multiple-choice	f	t	0	2017-12-09 00:47:39	2018-07-09 23:45:41	3	7pKA5DK3
169	BE12018-48	<p>The patient is a 68-year-old, right-handed male who presents to the emergency department following a tablesaw injury to his nondominant left index finger. The patient states that he was working at home after having &ldquo;a few&rdquo; beers, when his hand slipped and his nondominant index finger was drawn into the blade of the saw. On examination, he has sustained an amputation to the index finger at the mid-shaft of the proximal phalanx, with a stellate, multilevel soft tissue injury to the index finger base. Radiographs demonstrate a comminuted fracture of the proximal phalanx with intra-articular involvement and a fracture of the metacarpal head. The amputated index finger was irretrievable and not brought to the hospital.</p>	multiple-choice	t	f	0	2017-12-09 00:49:59	2018-11-20 05:18:02	3	AdknJykR
170	BE12018-57	\N	multiple-choice	f	t	0	2017-12-09 00:50:09	2018-07-14 07:56:23	3	QnB0QAkA
171	BE12018-56	<p>A 50-year-old female with multiple joint pain of the hand and feet. She experienced the pain symmetrically for the last 8 weeks and more in the proximal part of the joint. The pain is usually worsened early in the morning accompanied with stiffness after a period of inactivity. After lab exam and x-ray examination, the orthopedic surgeon diagnoses her with rheumatoid arthritis of the hand in the synovitis stage.</p>	multiple-choice	t	f	0	2017-12-09 00:55:35	2017-12-09 01:03:07	3	wdKvLGk2
172	BE12018-55	<p>A 50-year-old female with multiple joint pain of the hand and feet. She experienced the pain symmetrically for the last 8 weeks and more in the proximal part of the joint. The pain is usually worsened early in the morning accompanied by stiffness after a period of inactivity. After lab exam and x-ray examination, the orthopedic surgeon diagnoses her with rheumatoid arthritis of the hand in the synovitis stage.</p>	multiple-choice	t	f	0	2017-12-09 01:05:44	2017-12-09 05:57:21	3	j0gxZ5BM
173	BE12018-54	\N	multiple-choice	f	t	0	2017-12-09 05:58:29	2019-11-08 09:21:51	3	7pkZ1xB9
174	BE12018-53	<p>An 11-year-old girl came to the clinic with chief complain a crooked back. She has not had her period (menarche) yet. The physical examination showed that she still has a balanced shoulder with a prominent hump on the right thoracic area. X-ray evaluation depicts a structural thoracal curve apex in T8 with Cobb angle of 65◦ and non-structural lumbar curve apex in L3 with Cobb angle of 30◦. Further evaluation showed that the apex of the lumbar curve is still within central sacral line between the pedicles. Thoracic sagittal profile showed 20◦ of thoracic kyphosis.</p>	multiple-choice	t	f	0	2017-12-09 06:01:43	2018-11-20 05:17:50	3	j4B4A8kz
300	OBS-48	\N	multiple-choice	f	t	0	2018-05-20 15:25:28	2018-05-20 15:25:28	3	DjBr8ng2
301	OBS-49	\N	multiple-choice	f	t	0	2018-05-20 15:28:17	2018-05-20 15:28:17	3	VxgOApB3
175	BE12018-52	<p>A 6-year-old female comes to the clinic with her mother observing a thickening and slight enlargement on both of her knees with some bowing deformity. &nbsp;The enlargement is not severely progressive, however there also same bumps found around the wrist, the ankle and around the costochondral junction. Her mother also noticed that her child has smaller body compared to her friends.</p>	multiple-choice	t	f	0	2017-12-09 06:10:27	2018-11-20 05:17:42	3	0Qk8O0Bo
176	BE12018-51	<p>A six-year-old boy has been diagnosed with cerebral palsy and limited ambulatory function. Your physical exam found mild spasticity in all four extremities, with more involvement in the lower extremity compares to the upper extremities. The cognitive function of the patient is only mildly delayed for his chronologic age. In the pelvis radiograph, you found signs of dysplasia on bilateral hips.</p>	multiple-choice	t	f	0	2017-12-09 06:18:11	2018-11-20 05:17:34	3	JMBb02gp
177	BE12018-50	\N	multiple-choice	f	t	0	2017-12-09 06:24:27	2017-12-09 06:25:13	3	rxKXaqBm
178	BE12018-49	\N	multiple-choice	f	t	0	2017-12-09 06:27:44	2017-12-09 06:27:44	3	6yk550kb
179	BE12018-61	\N	multiple-choice	f	t	0	2017-12-09 06:30:43	2017-12-09 06:30:43	3	4Og9joK6
180	BE12018-62	<p>A 45-year-old man is brought to the trauma bay after falling from his roof while cleaning his utters. He complains of severe lower back pain but is neurologically intact. Initial CT images demonstrate an L1 burst fracture with a fracture through the lamina, 50% canal compromise from bony retropulsion, and 25 degrees of segmental kyphosis. An MRI demonstrates discontinuity of the ligamentum flavum and interspinous ligaments at the injured level.</p>	multiple-choice	t	f	0	2017-12-09 09:05:12	2017-12-10 02:02:16	3	x5gLzzB1
181	BE12018-50	<p>An 83-year-old female with a history of THR performed 14 years ago presents after an acute fall. She has a 2 year history of increasing thigh pain that initially began atraumatically. Figure below reveals a hip radiograph taken 1 month prior to the fall when she returned to her orthopaedic surgeon complaining of thigh pain. She has been unable to ambulate since the fall and presents to the emergency department for evaluation with new radiographs as seen figures below.</p>	multiple-choice	t	f	0	2017-12-09 09:12:00	2018-11-20 05:15:51	3	VbBaDXKw
318	OBS-66	\N	multiple-choice	f	t	0	2018-05-20 22:56:48	2018-05-20 22:56:48	3	nWkmjAg6
319	OBS-67	\N	multiple-choice	f	t	0	2018-05-20 22:58:50	2018-05-20 22:58:50	3	vAKz5ogj
196	BE12018-O1	<p>A 40-years-old female with a lump on the left wrist since 8 months ago. X-ray and histology slide shows in the figure above.</p>	essay	t	f	0	2017-12-10 03:25:14	2018-07-14 07:54:39	3	\N
197	BE12018-O2	<p>A 60-years-old female felt pain on the left humerus when she brings heavy object. She has a history of mastectomy of the left breast due to breast carcinoma 1 years ago and undergoing chemotherapy. Xray is shown in figure above</p>	essay	t	f	0	2017-12-10 03:31:42	2018-11-20 06:05:06	3	\N
199	BE12018-O3	<p>A 12-years-old boy came to orthopedic outpatient clinic because of pain in the right lower leg and left arm. X-ray is shown in figure above</p>	essay	t	f	0	2017-12-10 03:43:54	2018-11-20 06:04:35	3	\N
200	BE12018-O4	<p>The figure above shows histopathology result of two type of&nbsp;bone tumor.</p>	essay	t	f	0	2017-12-10 03:58:22	2018-11-20 06:04:03	3	\N
201	BE12018-O5	<p>A 9-years-old girl felt pain on the ankle since 1 year ago come to the outpatient clinic for medication.</p>	essay	t	f	0	2017-12-10 05:10:30	2018-07-14 07:53:06	3	\N
202	BE12018-S1	<p>This a photograph and X-ray appearance vertebra of a young lady, 16 years old who complaining of back pain since 6 months duration.</p>	essay	t	f	0	2017-12-10 05:20:00	2018-07-09 08:15:18	3	\N
203	BE12018-S2	<p>This imaging was&nbsp;taken from lumbosacral 53 years old male with low back pain for 3 months duration</p>	essay	t	f	0	2017-12-10 05:29:03	2018-11-20 06:02:02	3	\N
204	BE12018-H6	<p>This photograph was taken from a male 32 years old 4 days after surgery</p>	essay	t	f	0	2017-12-10 05:35:52	2018-07-14 07:52:52	3	\N
205	BE12018-H7	<p>This is photograph of patient with wound at his left axilla</p>	essay	t	f	0	2017-12-10 05:40:59	2018-07-09 08:18:34	3	\N
206	BE12018-L1	<p>This is a picture of knee assessment</p>	essay	t	f	0	2017-12-10 05:43:47	2018-07-09 08:17:37	3	\N
207	BE12018-62	<p>A 7 years old boy came to your clinical with a painful left elbow after a fall from bicycle 2 days ago. The swelling is not too obvious however he cannot flex the elbow due to pain. Neurovascular distal is intact. X-ray is as seen in the picture above.</p>	multiple-choice	t	f	0	2017-12-24 10:25:47	2018-11-20 05:14:16	3	\N
208	BE12018-63	<p>A newborn is referred to you due to swollen and inactive left upper extremity. The baby was born through spontaneous yet difficult delivery due to its huge birthweight.</p>	multiple-choice	t	f	0	2017-12-24 10:46:11	2018-07-09 23:52:04	3	\N
209	BE12018-64	<p>A 5 months old baby was admitted for to hospital for 3 days due to enteritis 3 weeks ago during which she had an intravenous fluid drip on her right ankle (saphenous vein). About one week ago she started to develop fever, irritable and swollen left knee. Today she comes to your clinic with clinical condition as follows</p>	multiple-choice	t	f	0	2017-12-24 10:56:11	2017-12-24 11:10:47	3	\N
210	BE12018-65	<p>A 10-year-old boy comes to your clinic with draining sinus on his outer left ankle. He had a high fever about 3 months&nbsp;ago during which his left ankle was swollen. At that time, he went to local primary health care to get treatment, the fever was resolved but the draining sinus continues</p>	multiple-choice	t	f	0	2017-12-24 11:11:10	2017-12-24 11:30:02	3	\N
211	BE12018-66	<p>A 12 years old boy came to the emergency department with a swollen right elbow after a fall. Neurovascular distal intact</p>	multiple-choice	t	f	0	2017-12-24 11:37:57	2018-11-20 05:13:48	3	\N
212	BE12018-67	\N	multiple-choice	f	t	0	2017-12-24 11:55:29	2018-11-20 05:13:31	3	\N
213	BE12018-68	\N	multiple-choice	f	t	0	2017-12-24 12:02:40	2017-12-24 12:02:40	3	\N
214	BE12018-69	<p>A 9-year-old boy comes to your clinic today with the history of fell onto outstretched right hand 3 weeks ago on a basketball match. He was brought to a local bonesetter, upon which his right wrist was splinted with a wooden bar. Today, he doesn&rsquo;t feel any pain anymore, however, the wrist range of motion is still limited</p>	multiple-choice	t	f	0	2017-12-24 12:07:49	2018-11-20 05:15:03	3	\N
215	BE12018-P1	<p>A 12 years old girl with spastic diplegic cerebral palsy come to your clinic complaining she has a scissoring gait. The pelvic x-ray is as follow</p>	essay	t	f	0	2017-12-24 12:17:40	2017-12-24 12:22:16	3	\N
216	BE12018-P2	<p>An obese 14 years old boy came to your clinic with intermittent and migrating pain in his right groin, hip, knee, and thigh for several weeks. This pain usually worsens with activity. He may still walk with a limp in a period of activity but then stopped due to pain. The pelvic x-ray is as above</p>	essay	t	f	0	2017-12-24 12:22:38	2018-11-20 06:07:49	3	\N
320	OBS-68	\N	multiple-choice	f	t	0	2018-05-20 23:00:46	2018-05-20 23:00:46	3	\N
321	OBS-69	\N	multiple-choice	f	t	0	2018-05-20 23:03:02	2018-05-20 23:03:02	3	\N
322	OBS-70	\N	multiple-choice	f	t	0	2018-05-20 23:06:30	2018-05-20 23:06:30	3	\N
183	BE12018-52	<p>A 42-year-old man presents to the hospital with pain and swelling of the dorsum of his hand. He reports blunt trauma against a metal shelf, but does not remember a break in the skin. There is a blister of the skin. He reports erythema started approximately 6 hours ago of the hand but it now extends to the wrist. He is febrile to 102 degrees, heart rate is 110, and blood pressure is 92/38. He has significant pain to palpation and induration of the dorsum of the hand.</p>	multiple-choice	t	f	0	2017-12-09 23:26:29	2018-11-20 05:15:23	3	RxBWbQKr
188	BE12018-57	\N	multiple-choice	f	t	0	2017-12-10 00:01:25	2017-12-10 00:01:25	3	DjBrM1K2
191	BE12018-H1	<p>A 54-year-old male presented to the ED with left elbow pain after sustaining an injury in a low-speed motor vehicle accident.</p>	essay	t	f	0	2017-12-10 02:50:11	2018-11-20 06:05:57	3	YRgyeLg6
192	BE12018-H2	<p>A 28-year-old, right-hand-dominant male caught big air going off a jump while snowboarding for the first time. He landed awkwardly on his non-dominant left hand and immediately developed pain</p>	essay	t	f	0	2017-12-10 02:57:03	2018-07-14 07:53:59	3	1OgdY9Kz
193	BE12018-H3	<p>A 34-year-old man presents to the emergency department with pain in his left index finger. He reports that he was cutting meat when his knife slipped and punctured the volar surface of his proximal phalanx.</p>	essay	t	t	0	2017-12-10 03:01:35	2018-11-20 06:06:53	3	pwBeX5KQ
194	BE12018-H4	<p>A mother brought her 8-month-old child with deformity disorder on the right-hand finger since birth</p>	essay	t	f	0	2017-12-10 03:06:02	2018-11-20 06:06:24	3	PNKG7XKn
195	BE12018-H5	<p>A 10-month-old, male child comes to your office accompanied by his parents who have recently noticed that while he is using his hand, he appears unable to extend his thumb fully</p>	essay	t	f	0	2017-12-10 03:12:30	2018-07-09 08:15:51	3	prK2Dekn
217	BE12018-P3	<p>An obese 5-year-old boy came with a complaint of left lower extremity being bow legged which has been worsened over 1 year. There is no sign inflammation or significant trauma to the area. The boy has also a problem with his body weight. He was an early walker as well.</p>	essay	t	f	0	2017-12-24 12:29:05	2018-07-09 08:16:12	3	\N
218	BE12018-P4	<p>A 7 years old boy came to your clinic with a painful left wrist after a fall on an outstretched hand in a soccer match an hour ago. Neurovascular distal is intact.</p>	essay	t	f	0	2017-12-24 13:38:56	2017-12-24 13:44:56	3	\N
219	BE12018-P5	<p>A newborn in the pediatric ward is referred to you with multiple bowings and the baby is irritable when the nurse tries to move its extremities. The babygram is as the picture&nbsp;above.</p>	essay	t	f	0	2017-12-24 13:47:38	2017-12-26 09:46:36	3	\N
221	BE12018-70	<p>A 7 years old boy came to your clinical with painful left elbow after a fall from bycycle 2 days ago. The swelling is not to obvious how ever he cannot flex the elbow due to pain. Neurovascular distal is intact. X ray is as follow</p>	multiple-choice	t	f	0	2017-12-27 10:38:03	2017-12-27 10:42:21	3	\N
225	BE12712-A1	<p>A 35-year-old-male complaining pain on the right knee. Pain especially during up and down stairs and unstable knee while walking.</p>\n\n<p>He had history&nbsp; of traffic accident 1 years ago.</p>	essay	t	f	0	2017-12-27 13:34:57	2017-12-27 14:16:49	3	\N
226	BE12712-A2	<p>Female, 42 years old came complaining pain at medial side of her forefoot. History of daily high heels usage is admitted. The clinical pictures is as follows.</p>	essay	t	f	0	2017-12-27 13:42:44	2017-12-27 14:16:56	3	\N
227	BE12712-A3	<p>A 35-year-old-female had motorcycle accident. Her knee was hit by another motorcycle from front.</p>	essay	t	f	0	2017-12-27 13:45:59	2017-12-27 14:17:04	3	\N
228	BE12712-H1	<div>&bull;Male 25 years old right handed male arrived at emergency room after accidentally he cut his own finger when working</div>	essay	t	f	0	2017-12-27 13:51:29	2017-12-27 13:53:37	3	\N
229	BE12712-H2	<p>33 years old right handed male complaining pain at his hand after Blowing a fist to a robber</p>	essay	t	f	0	2017-12-27 13:53:54	2017-12-27 14:17:12	3	\N
230	BE12712-H3	<p>45 years old right handed male complain about deformity of his right index finger. He say that 9 months ago, his deformed finger is only on the distal joint that cannot extend after being hit by a ball when trying to catch it.</p>	essay	t	f	0	2017-12-27 14:14:27	2017-12-27 14:17:17	3	\N
231	BE12712-O1	<p>Diagnosis : Pathologic fracture subtroanteric femur Dextra Mammae carcinoma</p>	essay	t	f	0	2017-12-27 14:22:57	2017-12-27 14:24:46	3	\N
232	BE12712-02	\N	essay	t	f	0	2017-12-27 14:25:02	2017-12-28 08:41:47	3	\N
233	BE12712-03	\N	essay	t	f	0	2017-12-27 14:27:50	2017-12-27 14:27:50	3	\N
234	281217-1	<p>A 30-year -old, right-hand-dominant man presents to the clinic complaining of anterior right shoulder pain. There is pain mostly with an overhead movement that radiates to the biceps muscle belly. He takes no medications, is otherwise healthy, and works as a car mechanic. He is an avid volleyball player. His examination includes a positive Hawkins test, positive Yerguson&#39;s test, tenderness to palpation over the intertubercular sulcus, and a negative Speed&#39;s test. The rest of the examination is normal. Plain radiographs are normal.</p>	multiple-choice	t	f	0	2017-12-27 14:37:36	2017-12-27 14:43:37	3	\N
235	281217-2	<p>An active, 19-year-old gymnast complains of ulnar-sided wrist pain.&nbsp; She has already obtained an MRI scan which reveals ECU tendinitis.</p>	multiple-choice	t	f	0	2017-12-27 14:44:08	2017-12-27 14:57:58	3	\N
236	281217-3	<p>A 7-year-old boy arrives at the emergency department with forearm pain. Today he was picking up his backpack when he felt a pop in his forearm that resulted in the current injury.&nbsp; His history is significant for 5 other fractures treated nonsurgically. His mother states that she had 14 fractures during childhood but is healthy now. Both the boy and his mother have blue sclera.&nbsp; Figures above are the radiographs of his injured forearm.</p>	multiple-choice	t	f	0	2017-12-27 15:01:38	2017-12-27 15:27:25	3	\N
237	281217-4	<p>A 56-year -old homemaker fell down the steps of her basement injuring her left ring finger. She was seen at an outside facility with significant deformity of the ring finger. There were no open wounds. There were severe pain and limited motion. Radiographs are shown in Figures above</p>	multiple-choice	t	f	0	2017-12-27 15:29:26	2017-12-27 15:35:43	3	\N
238	BE12712-P1	<p>A 4 years old boy cannot move his left elbow after a fall onto his left &nbsp;hand while the elbow is flexed</p>	essay	t	f	0	2017-12-27 15:59:22	2017-12-27 16:02:32	3	\N
239	BE12712-P2	<p>A two year old baby came to your clinic with a deformities on both of his feet</p>	essay	t	f	0	2017-12-27 16:02:47	2017-12-27 16:04:45	3	\N
240	BE12712-P3	<p>A 4 years ol girl came to your clinic with a short-leg gait of the left lower extremity. History shows that she was born with breech presentation. There is no history of pain or fever. Physical examination reveals a short-leg gait with 3 cm of leg length discrepancy. The trendelenburg sign on the left hip is positive with limited abduction</p>	essay	t	f	0	2017-12-27 16:04:59	2017-12-27 16:06:33	3	\N
241	BE12712-S1	<div>21-year-old female complain about severe back pain after her car hit a tree. There is no neurologic deficit. The sagittal CT of lumbar spine is showed in figure</div>	essay	t	f	0	2017-12-27 16:07:39	2017-12-27 16:08:47	3	\N
242	BE12712-S2	<div>A sagittal CT scan of a 77-year-old woman who has been experiencing back pain for about 1 month. No history of trauma, No muscle weakness and sensory disturbances.</div>	essay	t	f	0	2017-12-27 16:09:05	2017-12-27 16:10:24	3	\N
302	OBS-50	\N	multiple-choice	f	t	0	2018-05-20 15:30:46	2018-05-20 15:30:46	3	\N
243	BE12712-S3	<div>A 47-year-old man has with chief complain back pain for 6 months.&nbsp; Night pain are noted and pain in change of position. Motoric power&nbsp; of lower extremities are 4/5.&nbsp; MRI of thoracal spine is showed in this figure</div>	essay	t	f	0	2017-12-27 16:10:41	2017-12-30 22:29:24	3	\N
244	BE12018-A1	<p>This is a Radiological picture of a 53-year-old male with a left hip pain felt since 4 months before admission. The pain felt insidious, occurred when he was walking, decreased when resting. Now, he feels pain when he raises his leg. Walks with crutches. Fever (-),&nbsp; history of trauma (-), Cough (-), TB medication (-), alcohol (-) no history of the family member with the same disease, history of traditional medicine consumption and analgesic (+) since 5 years ago. Physical examination shows leg length discrepancy and tenderness on the right hip without mass and swelling. The range of motion was limited (see the picture above). Laboratory examination was performed with result,&nbsp;WBC 11.700, ESR 25, CRP 0,4, uric acid 21/23, and ICT TB was negative.&nbsp;</p>\n\n<div>&nbsp;</div>	essay	t	f	0	2017-12-27 16:21:56	2018-11-20 06:17:58	3	\N
245	BE12018-A2	<p>A-70-years old female complaining bilateral knee pain since 10 years ago. Pain is felt when she is walking and climbing the stairs. She also feels her leg is become bowed. No history of trauma. Clinical&nbsp;and radiological pictures show in the figure above. Varus deformity, patellar crepitation and limited range of motion were found in physical examination.&nbsp;</p>	essay	t	f	0	2017-12-27 16:25:35	2018-11-20 06:10:16	3	\N
246	BE12018-A3	<p>This is the clinical and radiological picture of a 68 y.o. female. She complained of thigh pain after slipping on the floor at home. She had a history of hip pain treated by herself using traditional medicine and painkillers since 33 years ago. She underwent total hip replacement 31 years ago because of a damaged hip (exact diagnosis is unknown)</p>	essay	t	f	0	2017-12-27 16:35:44	2018-11-20 06:09:46	3	\N
247	BE12018-A4	<p>This is the radiological and clinical picture of a 45 y.o. male with a chief complaint of swelling and discomfort on his right knee. He is unable to flex his knee maximally. No history of trauma or fever. He also felt a palpable mass on his right inguinal. Physical examination shows valgus deformity with edema and muscle atrophy,&nbsp;tenderness, warmth, doughy sign positive, no sinus, and range of motion from&nbsp;5-90 degrees. Patellar tap test was positive. Laboratory test found&nbsp;Hb 11,6 gr/dl, WBC 7,37, ESR 77, CRP 2,2 and ICT TB was positive.</p>	essay	t	f	0	2017-12-27 16:40:36	2017-12-30 21:56:08	3	\N
248	BE12018-A5	<p>This is the clinical and radiological picture of a 25 y.o male. He came with a complaint of a right knee pain and instability. There is a history of a direct trauma to the knee in a motorcycle accident 6 months ago. Physical examination shows posterior sagging and muscle atrophy, no tenderness, normal neurovascular distal, and range of motion from 0-120 degrees. The special test was performed with result anterior drawer test -/-, Lachman test -/-, posterior drawer test +++/-, and quadriceps active test +/-.</p>	essay	t	f	0	2017-12-27 16:49:10	2017-12-30 21:44:45	3	\N
249	BE12018-S3	<p>This figure above shows fluoroscopy finding during interventional pain management;</p>\n\n<p>&nbsp;</p>	essay	t	f	0	2017-12-29 23:16:43	2017-12-29 23:22:57	3	\N
250	BE12018-S4	<p>The picture above is A lateral Lumbar Spine X-ray</p>	essay	t	f	0	2017-12-29 23:23:45	2018-11-20 06:05:32	3	\N
251	BE12018-S5	\N	essay	t	f	0	2017-12-29 23:41:17	2018-01-04 05:47:17	3	\N
252	OBS-1	\N	multiple-choice	f	t	0	2018-05-20 00:00:12	2018-07-09 23:49:23	3	\N
253	OBS-2	\N	multiple-choice	f	t	0	2018-05-20 00:03:03	2018-05-20 00:03:03	3	\N
254	OBS-3	\N	multiple-choice	f	t	0	2018-05-20 00:05:03	2018-05-20 00:05:03	3	\N
255	OBS-4	\N	multiple-choice	f	t	0	2018-05-20 00:07:31	2018-05-20 00:07:31	3	\N
256	OBS-5	\N	multiple-choice	f	t	0	2018-05-20 00:09:59	2018-07-09 23:50:39	3	\N
257	OBS-6	\N	multiple-choice	f	t	0	2018-05-20 00:16:06	2018-05-20 00:16:06	3	\N
258	OBS-6	\N	multiple-choice	f	t	0	2018-05-20 12:46:12	2018-07-09 23:50:53	3	\N
259	OBS-7	\N	multiple-choice	f	t	0	2018-05-20 12:49:05	2018-05-20 12:49:05	3	\N
260	OBS-8	\N	multiple-choice	f	t	0	2018-05-20 12:57:23	2018-05-20 13:01:57	3	\N
261	OBS-9	\N	multiple-choice	f	t	0	2018-05-20 12:59:43	2018-05-20 12:59:43	3	\N
262	OBS-10	\N	multiple-choice	f	t	0	2018-05-20 13:02:19	2018-05-20 13:02:19	3	\N
263	OBS-11	\N	multiple-choice	f	t	0	2018-05-20 13:04:13	2018-05-20 13:04:13	3	\N
264	OBS-12	\N	multiple-choice	f	t	0	2018-05-20 13:22:32	2018-05-20 13:22:32	3	\N
265	OBS-13	\N	multiple-choice	f	t	0	2018-05-20 13:29:30	2018-05-20 13:29:30	3	\N
266	OBS-14	\N	multiple-choice	f	t	0	2018-05-20 13:32:15	2018-05-20 13:32:15	3	\N
267	OBS-15	\N	multiple-choice	f	t	0	2018-05-20 13:33:32	2018-05-20 13:33:32	3	\N
268	OBS-16	\N	multiple-choice	f	t	0	2018-05-20 13:38:04	2018-05-20 13:38:04	3	\N
269	OBS-17	\N	multiple-choice	f	t	0	2018-05-20 13:40:04	2018-05-20 13:40:04	3	\N
270	OBS-18	\N	multiple-choice	f	t	0	2018-05-20 13:42:17	2018-05-20 13:42:17	3	\N
271	OBS-19	\N	multiple-choice	f	t	0	2018-05-20 13:45:06	2018-05-20 13:45:06	3	\N
272	OBS-20	\N	multiple-choice	f	t	0	2018-05-20 13:47:12	2018-05-20 13:47:12	3	\N
273	OBS-21	\N	multiple-choice	f	t	0	2018-05-20 13:49:04	2018-05-20 13:52:40	3	\N
274	OBS-22	\N	multiple-choice	f	t	0	2018-05-20 13:57:11	2018-05-20 13:57:11	3	\N
275	OBS-23	\N	multiple-choice	f	t	0	2018-05-20 14:00:07	2018-05-20 14:00:07	3	\N
276	OBS-24	\N	multiple-choice	f	t	0	2018-05-20 14:02:33	2018-11-20 05:12:23	3	\N
277	OBS-25	\N	multiple-choice	f	t	0	2018-05-20 14:08:01	2018-05-20 14:08:01	3	\N
278	OBS-26	\N	multiple-choice	f	t	0	2018-05-20 14:13:39	2018-05-20 14:13:39	3	\N
279	OBS-27	\N	multiple-choice	f	t	0	2018-05-20 14:17:02	2018-05-20 14:17:14	3	\N
280	OBS-28	\N	multiple-choice	f	t	0	2018-05-20 14:19:16	2018-07-09 23:47:41	3	\N
281	0BS-29	\N	multiple-choice	f	t	0	2018-05-20 14:23:01	2018-05-20 14:23:01	3	\N
282	OBS-30	\N	multiple-choice	f	t	0	2018-05-20 14:25:08	2018-07-09 23:51:06	3	\N
283	OBS-31	\N	multiple-choice	f	t	0	2018-05-20 14:26:44	2018-05-20 14:29:43	3	\N
284	OBS-32	\N	multiple-choice	f	t	0	2018-05-20 14:32:22	2018-05-20 14:32:22	3	\N
285	OBS-33	\N	multiple-choice	f	t	0	2018-05-20 14:34:04	2018-05-20 14:34:04	3	\N
286	OBS-34	\N	multiple-choice	f	t	0	2018-05-20 14:35:46	2018-05-20 14:35:46	3	\N
287	OBS-35	\N	multiple-choice	f	t	0	2018-05-20 14:38:01	2018-05-20 14:38:01	3	\N
288	OBS-36	\N	multiple-choice	f	t	0	2018-05-20 14:40:16	2018-05-20 14:40:16	3	\N
289	OBS-37	\N	multiple-choice	f	t	0	2018-05-20 14:43:32	2018-05-20 14:43:32	3	\N
290	OBS-38	\N	multiple-choice	f	t	0	2018-05-20 14:45:40	2018-05-20 14:45:40	3	\N
291	OBS-39	\N	multiple-choice	f	t	0	2018-05-20 14:49:04	2018-05-20 14:49:04	3	\N
292	OBS-40	\N	multiple-choice	f	t	0	2018-05-20 14:51:32	2018-05-20 14:51:32	3	\N
293	OBS-41	\N	multiple-choice	f	t	0	2018-05-20 14:53:46	2018-05-20 14:53:46	3	\N
294	OBS-42	\N	multiple-choice	f	t	0	2018-05-20 14:56:12	2018-05-20 14:56:12	3	\N
295	OBS-43	\N	multiple-choice	f	t	0	2018-05-20 15:05:09	2018-05-20 15:05:09	3	\N
296	OBS-44	\N	multiple-choice	f	t	0	2018-05-20 15:13:20	2018-05-20 15:13:20	3	\N
297	OBS-45	\N	multiple-choice	f	t	0	2018-05-20 15:16:20	2018-05-20 15:16:20	3	\N
298	OBS-46	\N	multiple-choice	f	t	0	2018-05-20 15:18:13	2018-05-20 15:18:13	3	\N
299	OBS-47	\N	multiple-choice	f	t	0	2018-05-20 15:20:07	2018-05-20 15:20:07	3	\N
303	OBS-51	\N	multiple-choice	f	t	0	2018-05-20 15:33:00	2018-05-20 15:33:00	3	\N
304	OBS-52	\N	multiple-choice	f	t	0	2018-05-20 15:36:01	2018-05-20 15:36:01	3	\N
305	OBS-53	\N	multiple-choice	f	t	0	2018-05-20 22:19:27	2018-05-20 22:19:27	3	\N
306	0BS-54	\N	multiple-choice	f	t	0	2018-05-20 22:23:53	2018-05-20 22:23:53	3	\N
307	OBS-55	\N	multiple-choice	f	t	0	2018-05-20 22:28:03	2018-05-20 22:28:03	3	\N
308	OBS-56	\N	multiple-choice	f	t	0	2018-05-20 22:29:32	2018-05-20 22:29:32	3	\N
309	OBS-57	\N	multiple-choice	f	t	0	2018-05-20 22:31:41	2018-05-20 22:31:41	3	\N
310	0BS-58	\N	multiple-choice	f	t	0	2018-05-20 22:34:25	2018-05-20 22:34:25	3	\N
311	OBS-59	\N	multiple-choice	f	t	0	2018-05-20 22:36:31	2018-05-20 22:36:31	3	\N
312	OBS-60	\N	multiple-choice	f	t	0	2018-05-20 22:38:52	2018-05-20 22:38:52	3	\N
313	OBS-61	\N	multiple-choice	f	t	0	2018-05-20 22:41:00	2018-05-20 22:41:00	3	\N
314	OBS-62	\N	multiple-choice	f	t	0	2018-05-20 22:45:09	2018-05-20 22:45:09	3	\N
326	OBS-73	\N	multiple-choice	f	t	0	2018-05-20 23:20:38	2018-05-20 23:20:38	3	\N
327	OBS-74	\N	multiple-choice	f	t	0	2018-05-20 23:21:34	2018-05-20 23:21:34	3	\N
328	OBS-75	\N	multiple-choice	f	t	0	2018-05-20 23:24:45	2018-11-20 05:11:52	3	\N
329	obs-76	\N	multiple-choice	f	t	0	2018-05-20 23:28:48	2018-05-20 23:28:48	3	\N
330	OBS-77	\N	multiple-choice	f	t	0	2018-05-20 23:31:22	2018-05-20 23:31:22	3	\N
331	OBS-78	\N	multiple-choice	f	t	0	2018-05-20 23:34:57	2018-05-20 23:34:57	3	\N
332	OBS-79	\N	multiple-choice	f	t	0	2018-05-20 23:38:27	2018-05-20 23:38:27	3	\N
333	OBS-80	\N	multiple-choice	f	t	0	2018-05-20 23:49:55	2018-05-20 23:49:55	3	\N
334	METABOLIC -1	\N	multiple-choice	f	t	0	2018-05-20 23:52:00	2019-11-08 09:41:58	3	\N
335	OBS-81	\N	multiple-choice	f	t	0	2018-05-20 23:54:55	2018-11-20 05:11:39	3	\N
336	OBS-82	\N	multiple-choice	f	t	0	2018-05-20 23:57:29	2018-05-20 23:57:29	3	\N
337	METABOLIC-2	\N	multiple-choice	f	t	0	2018-05-20 23:59:09	2018-11-20 05:11:23	3	\N
338	OBS-83	\N	multiple-choice	f	t	0	2018-05-21 00:01:08	2018-11-20 05:11:31	3	\N
339	OBS-84	\N	multiple-choice	f	t	0	2018-05-21 00:03:02	2018-05-21 00:03:02	3	\N
340	OBS-85	\N	multiple-choice	f	t	0	2018-05-21 00:05:57	2018-05-21 00:05:57	3	\N
341	OBS-86	\N	multiple-choice	f	t	0	2018-05-21 00:07:50	2018-05-21 00:07:50	3	\N
342	OBS-87	\N	multiple-choice	f	t	0	2018-05-21 00:09:22	2018-11-20 05:11:04	3	\N
343	TRAUMA-1	\N	multiple-choice	f	t	0	2018-05-21 00:12:13	2018-05-21 00:12:13	3	\N
344	OBS-88	\N	multiple-choice	f	t	0	2018-05-21 00:16:14	2018-11-20 05:10:51	3	\N
345	OBS-89	\N	multiple-choice	f	t	0	2018-05-21 00:19:29	2018-05-21 00:19:29	3	\N
346	OBS-90	\N	multiple-choice	f	t	0	2018-05-21 00:22:28	2018-05-21 00:22:28	3	\N
347	OBS-91	\N	multiple-choice	f	t	0	2018-05-21 00:24:03	2018-11-20 05:10:39	3	\N
348	BE18718-UNHAS1	\N	multiple-choice	f	t	0	2018-07-01 09:54:16	2018-07-01 09:54:16	3	\N
349	BE18718-UNHAS2	\N	multiple-choice	f	t	0	2018-07-01 10:04:52	2018-11-20 05:38:53	3	\N
350	BE18718-UNHAS3	<p>A 44 year old, right-hand-dominant, male, sustains blunt trauma to his right arm while using a jackhammer. He has immediate pain and deformity to his right upper extremity and presents immediately to the emergency department with this isolated injury. Physical examination shows skin to be intact, and patient has wrist extensor weakness. Plain radiographs reveal a spiral fracture of the right humerus mid-diaphysis with 30 degrees of anterior and 30 degrees of varus angulation.</p>	multiple-choice	t	t	0	2018-07-01 10:07:05	2018-11-20 05:10:23	3	\N
351	BE18718-UNHAS4	\N	multiple-choice	f	f	0	2018-07-04 22:45:10	2018-07-04 22:49:44	3	\N
352	BE18718-UNHAS5	\N	multiple-choice	f	t	0	2018-07-04 22:52:08	2018-07-04 22:52:08	3	\N
353	BE18718-UNHAS6	<p>A 50-year-old otherwise healthy and active gentleman fell from a height of approximately 8 ft while rock climbing. He had immediate pain in his left ankle an was unable to bear weight on that extremity. He presented to the emergency department with pain isolated to the left ankle and foot. Initial radiographs of the left ankle are obtained and the lateral view is shown.</p>	multiple-choice	t	t	0	2018-07-04 22:54:22	2018-07-04 22:59:10	3	\N
354	BE18718-UNHAS7	\N	multiple-choice	f	t	0	2018-07-04 22:59:49	2018-07-04 22:59:49	3	\N
355	BE18718-UNHAS8	\N	multiple-choice	f	t	0	2018-07-04 23:02:35	2018-07-04 23:02:35	3	\N
356	BE18718-UNHAS9	\N	multiple-choice	f	t	0	2018-07-05 11:08:54	2018-07-05 11:08:54	3	\N
357	BE18718-UNHAS10	\N	multiple-choice	f	t	0	2018-07-05 11:11:25	2018-07-05 11:11:25	3	\N
358	BE18718-UNHAS11	<p>A 78-year-old woman is complaining of a new onset of mid-thoracic back pain after a fall from standing 2 days ago. She denies any radiating pain or paresthesias into the extremities. Physical examination demonstrates that she is neurologically intact and has localized tenderness over the thoracic spine. She remains ambulatory and has adequate pain relief with over-the-counter medications. Thoracic spine radiographs are shown in figure.</p>	multiple-choice	t	t	0	2018-07-05 11:13:32	2018-11-20 05:08:35	3	\N
359	BE18718-UNHAS12	<p>A 76-year-old, right-hand-dominant man presents to clinic complaining of right shoulder pain. The pain started several months ago, has gotten progressively worse and is located diffusely over his deltoid region. He has night pain and pain with overhead activity. On examination, there is no visible muscle atrophy, and he has full passive and near full active range of motion. He experiences pain and some weakness with resisted shoulder forward flexion and abduction.</p>	multiple-choice	t	t	0	2018-07-05 11:19:06	2018-11-20 05:08:21	3	\N
360	BE18718-UNHAS13	<p>A patient presents with a history of chronic wrist pain of 6 years duration. He stated that he sustained a fall 9 years ago. Immediately after injury, he did not seek any medical attention, thinking that he had merely sprained his wrist.</p>	multiple-choice	t	t	0	2018-07-05 11:25:04	2018-07-06 01:18:52	3	\N
361	BE18718-UNHAS14	<p>A 57-year-old female presents to the office with atraumatic knee pain. Her pain is worse at the beginning of activities as well as the end of the day. She has trouble ascending and descending stairs. She uses anti-inflammatories intermittently with mild relief. On examination she has pain along the medial joint line but full range of motion. X-rays show characteristic medial joint space narrowing, subchondral sclerosis, and cysts.</p>	multiple-choice	t	t	0	2018-07-05 11:31:09	2018-07-05 12:21:09	3	\N
458	BE191218-19	\N	multiple-choice	f	t	0	2018-12-02 20:05:28	2018-12-02 20:05:28	3	\N
362	BE18718-UNHAS15	<p>A 56-year-old male is referred to your office by his primary physician, with concern for metastatic disease. He has lytic lesions found throughout his skeleton, including in the left iliac crest and proximal femur. The iliac crest lesion is biopsied for diagnosis confirmation. Histology image is shown above (GAMBAR 1)</p>	multiple-choice	t	t	0	2018-07-05 12:41:05	2018-11-20 05:07:50	3	\N
363	BE18718-UNHAS16	<p>An 7-year-old girl is brought to the ER by her mother after she fell from the monkey bars during recess at school. She had immediate pain, deformity, and swelling of the elbow after the fall. Upon arrival to the ER, the following radiographs were obtained.</p>	multiple-choice	t	t	0	2018-07-05 12:44:41	2018-11-20 05:08:03	3	\N
364	BE18718-UNS1	<p>Preoperative MRI images are shown from a 67-year-old woman with neck pain, bilateral upper extremity paresthesias, progressively worsening balance, several falls, and increasing problems in both hands with dropping objects. Figure 1 is a sagittal view, Figure 2 is an axial cut at C2-3, Figure below is an axial cut at C5-6, and Figure 4 is an axial cut at C6-7. The patient&rsquo;s motor strength is grade 4+ of 5 in the bilateral upper extremities.</p>	multiple-choice	t	t	0	2018-07-06 01:19:24	2018-11-20 05:07:35	3	\N
365	BE18718-UNS2	\N	multiple-choice	f	t	0	2018-07-06 01:28:27	2018-11-20 05:21:38	3	\N
381	BE18718-A1	<p>A 75 year old male underwent total knee arthroplasty due to osteoarthritis of the right knee. During the surgery, after trial of the implant, the patella subluxes laterally during flexion.</p>	essay	t	f	0	2018-07-14 12:02:06	2018-07-14 21:26:26	3	\N
511	BE191218-100	\N	multiple-choice	f	t	0	2018-12-11 10:29:21	2018-12-11 10:29:21	3	\N
366	BE18718-UGM1	<p>A 25-year-old obese male is sent to your hospital with a diagnosis of bilateral close fracture of the femoral shaft following a road traffic accident. During the primary survey in the ER, you noticed that the patient looks pale with a confused and irritable condition. The extremities are cold with HR: 128 x/minutes, BP: 90/60 mmHg and urine output 15cc/Kg BW/ Hour. After the fluid resuscitation, the patient&rsquo;s condition improves and both of the extremity are put in a traction for provisional immobilization</p>	multiple-choice	t	t	0	2018-07-06 02:52:22	2018-11-20 05:07:21	3	\N
367	BE18718-UGM2	<p>A 45-year-old male has a motorcycle accident and is diagnosed as Left tibial plateau fracture Schatzker 6. In the emergency department, the patient was put in a long leg posterior splint and scheduled for internal fixation the next morning</p>	multiple-choice	t	f	0	2018-07-06 03:06:42	2018-07-06 03:17:09	3	\N
368	BE18718-UGM3	<p>A 34-year-old male come to the orthopedic outpatient clinic with a chief complain of pain and deformity in his back. The pain started with discomfort since 6 months ago and increase as time goes by, predominantly during activity and relieve in resting. He also felt general weakness, night sweats and weight loss with visible a hard lump in the middle part of the back. Your physical finding showed kyphosis with apex around Th 11 with a decrease of ASIA score distal to Th 12 level. The laboratory examination suggests tuberculosis with MRI showing increase T2 weighted image in Th11-12 with abscess formation surrounding the vertebrae and narrowing the spinal canal.</p>	multiple-choice	t	f	0	2018-07-06 03:17:55	2018-11-20 05:07:08	3	\N
369	BE18718-UGM4	<p>A 56-year-old female came to the orthopedic outpatient clinic with a chief of complaint of severe pain in her back with numbness and weakness on both of the lower extremity. From the history taking you notice that the patient was diagnosed with DDD and protrusion of the L4-L5 disc that underwent transforaminal injection 2 weeks prior to admission. From the MRI you noticed increased intensity throughout the L4-L5 disc with the posterior epidural extension that encroaches the canal. The laboratory examination showed leucocyte count 21.300/mm3 and ESR 15 mg/ml</p>	multiple-choice	t	f	0	2018-07-06 03:27:41	2018-11-20 05:21:25	3	\N
370	BE18718-UGM5	<p>A 45-year-old male patient comes with main complain of disturbed gait with increased stiffness on both of lower extremity. The complaint is felt on activity and progress over time, 3 months before the disturbed walk he felt the loss of dexterity and have difficulties in buttoning his shirt. From the physical examination, you found that the patient is still able to ambulate independently without walking aid. You also found shuffling gait with increase physiologic reflex on both of lower extremity. From the supporting examination, you find continuous extending ossification of the PLL from C3 to C6 that narrowed the spinal canal.</p>	multiple-choice	t	f	0	2018-07-06 06:28:42	2018-11-20 05:06:58	3	\N
371	BE18718-UGM6	<p>A 65-year-old lady comes to your clinic for the second opinion related to the condition of her knee. She was previously diagnosed with osteoarthritis of the left knee and told to have a knee replacement, due to some hesitation she comes to your clinic to ask for more information. She is an active lady (normal BMI) that still do some Tennis on her spare time, however, during the last 6 months, she often feels knee pain the next morning after playing Tennis. The physical examination showed that her BMI is normal and the ROM of the knee is good. Her x-ray suggests mild osteophyte formation with slight narrowing in the medial part of the knee. She told you that she never take any medicine of physiotherapy related to her complain</p>	multiple-choice	t	f	0	2018-07-06 06:39:11	2018-11-20 05:05:38	3	\N
372	BE18718-UGM7	<p>A 35-year-old male wrestler comes to your clinic with a chief of complain pain and bulging in the anterior part of his knee. The pain is felt during kneeling position due to bulging mass on the anterior part of his patella. He complained that the mass occurred about 3 days ago after kneeling exercise and feel pain afterward. From physical examination, you feel smooth round mass with a diameter of 3 cm on the anterior part of the patella with mobile and fluctuation of the mass.</p>	multiple-choice	t	f	0	2018-07-06 06:49:29	2018-07-06 06:53:41	3	\N
373	BE18718-UGM8	<p>A 13-year-old girl presents with an isolated distal femur mass that was diagnosed as osteosarcoma. From the radiologic investigation, the mass has already extended into the surrounding soft tissue. Further work-up is negative for metastasis, with biopsy reveals a high-grade lesion.</p>	multiple-choice	t	f	0	2018-07-06 06:54:06	2018-11-20 05:05:12	3	\N
374	BE18718-H1	<div>37 yo male, right-hand-dominant male is referred to the ER after sustaining injury on his left ring finger.</div>	essay	t	f	0	2018-07-13 09:28:52	2018-11-20 05:52:24	3	\N
375	BE18718-H2	<p>A 29 y.o.&nbsp;male sustained injury to his right elbow during Motor Vehicle Accident. The X-ray of this elbow shown below.</p>	essay	t	t	0	2018-07-13 09:34:27	2018-07-14 21:19:15	3	\N
376	BE18718-H3	<p>40 y.o. male worked in Factory got his left hand trapped in machinery for 2 hours. He managed to release his hand afterwards. He felt excruciating pain and can&rsquo;t feel his fingers. Analgetic given only gave little relieve of his pain.</p>	essay	t	f	0	2018-07-14 09:03:33	2018-07-14 21:22:53	3	\N
377	BE18718-H4	<p>21 y.o. Male, Right handed, suffered injury into his left thumb during soccer game. He is a keeper, and got forced thumb abduction when catching the ball.</p>	essay	t	f	0	2018-07-14 09:22:46	2018-07-14 21:23:04	3	\N
378	BE18718-H5	<div>A patient presents with a history of chronic wrist pain of 5 years duration. He sustained a fall 9 years ago. Immediately after injury, he did not seek any medical attention, thinking that he had merely sprained his wrist.</div>	essay	t	f	0	2018-07-14 09:24:52	2018-11-20 05:53:08	3	\N
457	BE191218-15/16/17/18	<p>A 4 year old boy come to the emergency department and diagnose with closed fracture of the left supracondylar humerus extension type Gartland III, From the physical examination you also find swelling and positive Pucker sign in the anterior part of the distal humerus.</p>	multiple-choice	t	f	0	2018-12-02 19:59:33	2018-12-02 20:05:01	3	\N
379	BE18718-H6	<div>A 71 y o male complaining 2-year history of bilateral hand pain. He has morning stiffness in both of his hands, which lasts longer than 1 hour. No trauma to the hands before.</div>\n\n<div>The pain is mostly in his PIP and MCP of both hands. He has general aches and pains all over, but the hands are the worst.</div>\n\n<div>There is swollen, tender PIP and MCP joints bilaterally with some ulnar deviation. The DIP joints seem to be normal. He also has a small, nontender, rubbery nodule near his elbow. Neurovascular exam is normal.</div>	essay	t	f	0	2018-07-14 09:29:43	2018-07-14 21:23:50	3	\N
380	BE18718-H7	<div>A 33 y.o. Male sustained a gunshot wound at the elbow joint 10 months ago. No fracture was seen, and he has had no surgery. Now he complains of the inability to fully extend the wrist and all digits.</div>\n\n<div>&nbsp;</div>\n\n<div>Physical exam reveals 5/5 elbow flexion and extension but no function of the wrist extensors, EDC, or extensor pollicis longus.</div>\n\n<div>&nbsp;</div>\n\n<div>He has diminished sensation on the dorsal aspect of the radial hand and thumb. He has full passive ROM of the digits and wrist flexion and lacks 10 degrees of passive wrist extension.</div>	essay	t	f	0	2018-07-14 09:32:06	2018-07-14 21:23:55	3	\N
382	BE18718-A2	<div>A 61 years old lady complained about pain on her left hip due to osteoarthritis. She underwent elective hybrid total hip replacement surgery under combine spinal epidural anaesthesia. During the surgery, upon insertion of the femoral stem, her heart rate suddenly drop from 100 to 55 per minute, oxygen saturation fell to 76% and she became unresponsive.</div>	essay	t	f	0	2018-07-14 21:26:53	2018-11-20 06:00:09	3	\N
383	BE18718-A3	<div>A 25 years old male complains of &lsquo;giving way&rsquo; feeling on the right knee. 3 weeks ago, his knee was swollen after falling down and hit the ground when he was playing basketball. From the physical examination at the time of the injury, by looking there&rsquo;s asymmetric knee, hyperemia, swelling (+), feel tenderness (+), and&nbsp; limited range of movement, lack of extension. From the special test: Balotement (+), Cross fluctuation test (+), Patellar tap test (+)</div>	essay	t	f	0	2018-07-14 21:29:04	2018-11-20 05:59:35	3	\N
384	BE18718-A4	<p>A 69 years old female presents with swelling and pain on her calf 8 days after total knee arthroplasty. Clinical picture showed that there&rsquo;s swelling (+), bruise (+), tenderness (+), palpable distal pulse (+), and limited range of motion. From the special test: Homan&rsquo;s sign (+), squeeze test (+). D-Dimer level = 3 ug/ml</p>	essay	t	f	0	2018-07-14 21:32:28	2018-07-14 21:35:34	3	\N
385	BE18718-A5	<p>A 45-year-old gentlemen presented with pain and swelling in the arm after arm wrestling with his neighbor, around two months ago.</p>\n\n<p>&nbsp;</p>	essay	t	f	0	2018-07-14 21:35:52	2018-11-20 05:53:52	3	\N
386	BE18718-O1	<p>A 30-years-old male complaining lump in his right wrist joint since 9 months ago.</p>	essay	t	f	0	2018-07-14 21:41:40	2018-11-20 05:57:08	3	\N
387	BE18718-O2	<p>A 50-years old male suddenly suffering severe pain on his bilateral arm</p>	essay	t	f	0	2018-07-15 08:18:02	2018-07-15 08:21:34	3	\N
388	BE18718-03	<p>A 35-years old female comes to private clinic with pain and lump on her left ankle joint since 10 months ago.</p>	essay	t	f	0	2018-07-15 08:22:21	2018-11-20 05:56:20	3	\N
389	BE18718-O4	<p>A 53-years old female have mild pain on his right hip since 2 months. She was diagnosed with breast carcinoma 1 year ago and underwent surgery and chemotherapy.</p>	essay	t	f	0	2018-07-15 08:44:41	2018-07-15 08:46:53	3	\N
390	BE18718-O5	\N	essay	t	f	0	2018-07-15 08:47:38	2018-07-16 13:18:22	3	\N
391	BE18718-P1	<p>A 6 yo child fell down 1,5 years ago while playing football, he was brought to a bonesetter and was splinted for 6 weeks. Up until now he still feels his left elbow is unstable</p>	essay	t	f	0	2018-07-15 08:52:33	2018-11-20 05:58:01	3	\N
392	BE18718-P2	<p>A 11 yo boy had an antalgic gait since a few months ago. There was no history of hip major trauma. The gait got worst and he now barely walks. Left hips reveals significant limitations of internal rotation and abduction.</p>	essay	t	f	0	2018-07-15 08:59:14	2018-11-20 06:14:01	3	\N
393	BE18718-P3	<p>A 8 year old boy presented with short stature and bilateral genu valgus. Laboratory shows elevated serum phosphatase alkaline (2000 IU/L)</p>	essay	t	f	0	2018-07-15 09:29:28	2018-07-15 09:35:24	3	\N
394	BE18718-P4	<p>A 15 year old female with history of prematurity and prolonged ventilatory assisted hospitalization in NICU, presented to your clinic with spastic muscles, wheel chair dependendent and anxiety most likely due to hip pain</p>	essay	t	f	0	2018-07-15 09:37:08	2018-07-15 09:38:52	3	\N
395	BE18718-P5	<p>A 12 yo boy presented to your clinic with discharging sinus for the last 5 years. There was a history of fell down, fever and swollen right knee before it started to discharge</p>	essay	t	f	0	2018-07-15 09:39:33	2018-11-20 05:59:06	3	\N
396	BE18718-S1	<p>This is&nbsp; a CT scan imaging of a 27 years old male suffered from total paraplegia of both lower extremities after RTA</p>	essay	t	f	0	2018-07-15 09:41:35	2018-11-20 05:55:24	3	\N
397	BE18718-S2	<p>A 69-year-old woman&nbsp; has&nbsp; gait and balance difficulties after falling down. Physical examination reveals&nbsp; weakness in&nbsp; upper extremity weakness (1/5) and lower extremity (3/5). The MRI result shown above</p>	essay	t	f	0	2018-07-15 09:45:52	2018-07-15 09:51:42	3	\N
398	BE18718-S3	<p>In spinal column biomechanics&nbsp;</p>	essay	t	f	0	2018-07-15 09:52:23	2018-07-15 09:54:00	3	\N
399	BE18718-S4	<p>These are the radiograph and CT and MRI scans of a 35-year-old man with diabetes mellitus.&nbsp; He had severe neck pain for 6 weeks.&nbsp; He now has fevers and progressive weakness and numbness in his upper extremities.&nbsp; Examination reveals 3/5 strength in both upper extremities, with decreased sensation in both arms and hands and hyperreflexia.</p>	essay	t	f	0	2018-07-15 09:54:30	2018-11-20 05:54:43	3	\N
400	BE18718-S5	<p>These are the lumbar CT scans of a 16-year-old baseball pitcher who has had low-back pain for 3 months.&nbsp; He has no radiating pain, numbness, or weakness.&nbsp; His pain is worsened by extension and relieved with flexion.&nbsp; Examination reveals normal strength and sensation in his lower extremities.</p>\n\n<p>&nbsp;</p>	essay	t	f	0	2018-07-15 10:01:16	2018-11-20 05:54:15	3	\N
401	BE18718-USU1	\N	multiple-choice	f	t	0	2018-07-15 19:03:09	2018-07-15 19:03:09	3	\N
402	BE18718-USU2	\N	multiple-choice	f	t	0	2018-07-15 19:07:47	2018-07-15 19:07:47	3	\N
403	BE18718-USU3	\N	multiple-choice	f	t	0	2018-07-15 19:09:31	2018-07-15 19:09:31	3	\N
404	BE18718-USU4	\N	multiple-choice	f	t	0	2018-07-15 19:12:30	2018-07-15 19:12:30	3	\N
406	BE18718-UI1	\N	multiple-choice	f	t	0	2018-07-15 19:21:36	2018-07-15 19:21:36	3	\N
408	BE18718-UI2	<p>A 5-year-old boy has been febrile for the past 2 days, after an upper respiratory tract infection 7 days ago. He refuses to walk when you see him in your consultation room. He holds his left leg in frog legged position (The hip is flexed, externally rotated and abducted) and he will not let you examine him. His temperature is 39&deg;C while his white cell count is 20.&thinsp;000 WBC/mm 3, Erythrocyte sedimentation rate (ESR) is 60 mm/h, and C-reactive protein (CRP) is 70 mg/dL.</p>	multiple-choice	t	f	0	2018-07-15 19:24:21	2018-11-20 05:02:33	3	\N
409	BE18718-UI3	<p>A 7-year-old boy comes to your clinic because he started to be running strangely when playing outside a few weeks ago. Eventhough there is no sigificant major trauma to the hip, the problem got worst since then and he now walks with shorter steps with his left leg and spends less of the stance phase on that limb. Examination of bilateral hips reveals significant limitations of left hip in internal and external rotation and abduction. The x ray is as follows</p>	multiple-choice	t	f	0	2018-07-15 19:29:46	2018-07-15 19:36:54	3	\N
410	BE18718-UI4	<p>A 12-year-old girl comes to your clinic as a wheelchair-dependent and spastic child. She has a history of anoxia as an infant and prolonged hospitalization in NICU under ventilator. Hip abduction of the left hip is less than 30&deg;, Tardieu scale R1-R2 less than 15&deg;. The pelvic x ray is as follow</p>	multiple-choice	t	f	0	2018-07-16 13:27:06	2018-11-20 05:03:00	3	\N
411	BE18718-UI5	<p>The emergency department calls you to see a 12-year-old male with pain on his proximal left&nbsp; thigh. He was hit by a car while riding bycycle this morning. He refuses to move his left hip joint. The CT scan is as follow</p>	multiple-choice	t	f	0	2018-07-16 13:33:45	2018-11-20 05:01:44	3	\N
504	BE191218-93	\N	multiple-choice	f	t	0	2018-12-11 09:33:56	2018-12-11 09:33:56	3	\N
412	BE18718-UI6	<p>A 4 year old girl comes to your clinic unable to flex her left elbow after a fall in the playground 2 days ago. There is a slight tenderness on the lateral part of the elbow. The x ray is as follow</p>	multiple-choice	t	f	0	2018-07-16 13:37:39	2018-11-20 05:01:18	3	\N
413	BE18718-UI7	\N	multiple-choice	f	f	0	2018-07-16 13:46:09	2018-07-16 13:46:09	3	\N
414	BE18718-UI8	\N	multiple-choice	f	f	0	2018-07-16 13:48:29	2018-11-20 05:37:24	3	\N
415	BE18718-UI9	\N	multiple-choice	f	f	0	2018-07-16 13:54:04	2018-07-16 13:54:04	3	\N
417	BE18718-USU4	\N	multiple-choice	f	t	0	2018-07-16 19:50:00	2018-07-16 19:50:00	3	\N
418	BE18718-UI45	\N	multiple-choice	f	t	0	2018-07-16 20:13:39	2018-07-16 20:13:39	3	\N
419	BE18718-UI48	\N	multiple-choice	f	t	0	2018-07-16 20:16:22	2018-07-16 20:16:22	3	\N
420	BE18718-UI50	\N	multiple-choice	f	t	0	2018-07-16 20:19:22	2018-11-20 05:20:20	3	\N
421	BE18718-UI51	\N	multiple-choice	f	t	0	2018-07-16 20:22:30	2018-07-16 20:22:30	3	\N
422	BE18718-UI54	\N	multiple-choice	f	t	0	2018-07-16 20:24:44	2018-07-16 23:48:34	3	\N
423	BE18718-UI55	\N	multiple-choice	f	t	0	2018-07-16 20:29:07	2018-07-16 23:48:14	3	\N
424	BE18718-UI57	\N	multiple-choice	f	t	0	2018-07-16 23:49:06	2018-11-20 05:38:10	3	\N
425	BE18718-UI60	\N	multiple-choice	f	t	0	2018-07-16 23:51:21	2018-07-16 23:51:21	3	\N
426	BE18718-UI61	\N	multiple-choice	f	t	0	2018-07-17 00:01:10	2018-11-20 05:20:28	3	\N
427	BE18718-UI64	\N	multiple-choice	f	t	0	2018-07-17 00:04:16	2018-11-20 05:37:45	3	\N
428	BE18718-UI65	\N	multiple-choice	f	t	0	2018-07-17 00:06:00	2018-07-17 00:06:00	3	\N
429	BE18718-UI66	\N	multiple-choice	f	t	0	2018-07-17 00:08:34	2018-07-17 00:08:34	3	\N
430	BE18718-UI74	\N	multiple-choice	f	t	0	2018-07-17 00:11:37	2018-07-17 00:11:37	3	\N
431	BE18718-UI75	\N	multiple-choice	f	t	0	2018-07-17 00:15:03	2018-07-17 00:15:03	3	\N
432	BE18718-UI76	\N	multiple-choice	f	t	0	2018-07-17 00:18:00	2018-07-17 00:18:00	3	\N
433	ELECTIVE BALI 1	<p>A 4 months old baby with the deformity on both of his feet. The baby is active and able to roll over.</p>	essay	t	f	0	2018-08-04 07:22:38	2018-08-04 08:07:01	3	\N
434	BE191218-HAND1	<p>A 42 years old female maid complaining pain at her radial sided wrist, she denies previous trauma</p>	essay	t	f	0	2018-12-02 09:57:15	2018-12-02 10:12:02	3	\N
435	BE191218-HAND2	<div>An 18-months-old boy comes to your office accompanied by his parents who have recently noticed that while he is using his hand, he appears unable to extend his thumb fully.</div>\n\n<p>The child is able to use his hand very well, does not appear to be in any pain. The x-ray is shown no abnormality.</p>	essay	t	f	0	2018-12-02 10:12:24	2018-12-02 10:16:04	3	\N
436	BE191218-HAND3	<p>A boy, 10 y.o, came with prominence in front of right elbow. History of trauma 3 years ago after fell from his bike. No pain with full ROM</p>	essay	t	f	0	2018-12-02 10:16:22	2018-12-02 10:20:24	3	\N
437	BE191218-hand4	<div>A 34-year-old man presents to the emergency department with pain in his left small finger. He reports that he was cutting meat when his knife slipped and punctured the volar surface of his proximal phalanx. It did not bleed, and he did not seek further medical treatment.</div>\n\n<p>He presents with pain in passive extension of the finger, fusiform swelling.</p>	essay	t	f	0	2018-12-02 10:22:28	2018-12-11 13:11:17	3	\N
438	BE191218-HAND5	<p>A 50 year old male. Patient with mid shaft humerus fracture nine months ago. Without radial nerve lesion</p>\n\n<p>Prior U-slab fixation.</p>\n\n<p>Patient refused surgery.</p>	essay	t	f	0	2018-12-02 10:24:59	2018-12-02 10:28:38	3	\N
439	BE191218-SPINE1	<p>Male 26 years old, have a stab wound on posterior of&nbsp; the neck, after street&nbsp; fighting&nbsp; two hours before admission. Physical examination reveal total&nbsp; right hemiplegia , no abnormal finding in the cranial nerves.</p>	essay	t	f	0	2018-12-02 11:17:54	2018-12-02 11:27:19	3	\N
440	BE191218-SPINE2	<p>Female 42 years old , come to A&amp;E Dept with&nbsp; main complain severe pain on right lower&nbsp; leg and unable to extend the right big toe&nbsp; ( 2/5, LMN type) and micturition problem</p>	essay	t	f	0	2018-12-02 11:27:37	2018-12-02 11:30:34	3	\N
441	BE191218-SPINE3	<p>This is&nbsp; a CT scan imaging of a 27 years old male suffered from total paraplegia of both lower extremities after RTA</p>	essay	t	f	0	2018-12-02 11:30:51	2018-12-02 11:33:20	3	\N
442	BE191218-SPINE4	<p>Regarding this injury</p>	essay	t	f	0	2018-12-02 11:33:37	2018-12-02 11:36:18	3	\N
443	BE191218-SPINE5	<p>A 64-year-old man admitted to A&amp;E Departement with severe back pain since 5 months ago,&nbsp; but since a weeks ago ,&nbsp; he got fevers, and chills. He also has difficulty in walking (muscle power 3/5), No history of trauma, he have been suffered of diabetes mellitus since 5 years ago. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>\n\n<p>Vital Sign : BP : 120/80, pulse : 98/mnt, RR : 16/mnt, Temperature : 39,8⁰C</p>\n\n<p>Laboratory finding :&nbsp; Hb : 11,2 ; Leuco :&nbsp; 21.000; ESR: 105 ; Blood Glucose 315 ; Creatinine : 2,4</p>	essay	t	f	0	2018-12-02 11:36:36	2018-12-02 11:41:49	3	\N
444	BE191218-1	\N	multiple-choice	f	t	0	2018-12-02 19:20:00	2018-12-02 19:20:00	3	\N
445	BE191218-2	\N	multiple-choice	f	t	0	2018-12-02 19:22:42	2018-12-02 19:22:42	3	\N
446	BE191218-3	\N	multiple-choice	f	t	0	2018-12-02 19:24:21	2018-12-02 19:24:21	3	\N
447	BE191218-4	\N	multiple-choice	f	t	0	2018-12-02 19:26:18	2018-12-02 19:26:18	3	\N
448	BE191218-5	\N	multiple-choice	f	t	0	2018-12-02 19:27:54	2018-12-02 19:27:54	3	\N
449	BE191218-6	\N	multiple-choice	f	t	0	2018-12-02 19:29:20	2018-12-02 19:29:20	3	\N
450	BE191218-7	\N	multiple-choice	f	t	0	2018-12-02 19:30:59	2018-12-02 19:30:59	3	\N
451	BE191218-8	\N	multiple-choice	f	t	0	2018-12-02 19:32:54	2018-12-02 19:32:54	3	\N
452	BE191218-9	\N	multiple-choice	f	t	0	2018-12-02 19:34:35	2018-12-02 19:34:35	3	\N
453	BE191218-10/11	<p>Mrs. Heni came to your clinic bring her 2 months baby with right fracture that happened while she taking bath her baby. This is the third time it happened the first on left leg, the second time in on right lower leg. On Examination you find blue sclerae.</p>	multiple-choice	t	f	0	2018-12-02 19:36:34	2018-12-02 19:39:27	3	\N
454	be191218-12	\N	multiple-choice	f	t	0	2018-12-02 19:39:52	2018-12-02 19:39:52	3	\N
455	BE191218-13	\N	multiple-choice	f	t	0	2018-12-02 19:45:47	2018-12-02 19:45:47	3	\N
456	BE191218-14	\N	multiple-choice	f	f	0	2018-12-02 19:57:20	2018-12-02 19:57:20	3	\N
459	BE191218-20	\N	multiple-choice	f	t	0	2018-12-02 20:15:15	2018-12-02 20:15:15	3	\N
460	be191218-21	\N	multiple-choice	f	t	0	2018-12-02 20:17:33	2018-12-02 20:17:33	3	\N
461	BE191218-22	\N	multiple-choice	f	t	0	2018-12-02 20:20:11	2018-12-02 20:20:11	3	\N
505	BE191218-94	\N	multiple-choice	f	t	0	2018-12-11 09:54:10	2018-12-11 09:54:10	3	\N
506	BE191218-95	\N	multiple-choice	f	t	0	2018-12-11 10:16:28	2018-12-11 10:16:28	3	\N
507	BE191218-96	\N	multiple-choice	f	t	0	2018-12-11 10:17:56	2018-12-11 10:17:56	3	\N
508	BE191218-97	\N	multiple-choice	f	t	0	2018-12-11 10:23:48	2018-12-11 10:23:48	3	\N
509	BE191218-98	\N	multiple-choice	f	t	0	2018-12-11 10:26:03	2018-12-11 10:26:03	3	\N
510	BE191218-99	\N	multiple-choice	f	t	0	2018-12-11 10:27:50	2018-12-11 10:27:50	3	\N
462	BE191218-23/24/25/26	<p>A 25 year old female was referred to the emergency of your hospital following a RTA. She was a passenger of a car that had a head to head collision with other car. The physical examination of general condition showed the patient look painful with HR: 112x/min, RR: 28x/min, BP: 110/80 mmHg and T: normal. From the spine evaluation you notice loss of sensory and motor function below Th 10 with seatbelt mark on the anterior part of the chest wall. From the x ray you see posterior to anterior disruption with opening of PLC until the 2/3 of vertebral disc between the levels of Th9 &ndash; Th10</p>	multiple-choice	t	f	0	2018-12-02 20:22:56	2018-12-02 20:28:35	3	\N
463	BE191218-27/28/29/30/31	<p>A 19 year old female runner come to your clinic with pain and discomfort on her right forefoot. She experienced the pain after following intense training to prepare for a marathon for the last 6 months. The pain is felt on and off in the distal part of the 2nd and 3rd metatarsal, occasionally occurred with swelling after her running practice. Despite the condition after the training the patient still able to perform her daily activity without pain.</p>	multiple-choice	t	f	0	2018-12-02 20:30:20	2018-12-02 20:37:07	3	\N
464	BE191218-35/36	<p>A 23 year old male come to the clinic complaining the 5th time of having a shoulder dislocation. The last dislocation happened after brushing his hair and spontaneously reduce around 5 minutes afterward.</p>	multiple-choice	t	f	0	2018-12-03 14:09:23	2018-12-03 14:13:39	3	\N
465	BE191218-37/38	<p>A mother come to your clinic complaining her 7 day old baby with a flail right upper extremity. She noticed that the baby&rsquo;s right upper extremity look less active than the left side since after the birth, she also said that it is her first child and the baby birth weight was 4.200 gr with a breech presentation. From the physical examination you found that the shoulder look abnormally adducted and forearm internally rotated.</p>	multiple-choice	t	f	0	2018-12-03 14:14:23	2018-12-03 14:17:40	3	\N
466	BE191218-39	\N	multiple-choice	f	t	0	2018-12-03 14:17:58	2018-12-03 14:17:58	3	\N
467	BE191218-40	\N	multiple-choice	f	t	0	2018-12-03 14:22:09	2018-12-03 14:22:09	3	\N
468	BE191218-41	\N	multiple-choice	f	t	0	2018-12-03 14:25:26	2018-12-03 14:25:26	3	\N
469	BE191218-42	\N	multiple-choice	f	t	0	2018-12-03 14:27:12	2018-12-03 14:27:12	3	\N
470	BE191218-43	\N	multiple-choice	f	t	0	2018-12-03 14:29:33	2018-12-03 14:29:33	3	\N
471	BE191218-44	\N	multiple-choice	f	t	0	2018-12-03 14:31:36	2018-12-03 14:31:36	3	\N
472	BE191218-45	\N	multiple-choice	f	t	0	2018-12-03 14:33:57	2018-12-03 14:33:57	3	\N
473	BE191218-46	\N	multiple-choice	f	t	0	2018-12-03 14:35:37	2018-12-03 14:35:37	3	\N
474	BE191218-47	\N	multiple-choice	f	t	0	2018-12-03 14:37:24	2018-12-03 14:37:24	3	\N
475	BE191218-48	\N	multiple-choice	f	t	0	2018-12-03 16:04:09	2018-12-03 16:04:09	3	\N
476	BE191218-49	\N	multiple-choice	f	t	0	2018-12-03 16:05:57	2018-12-03 16:05:57	3	\N
477	BE191218-50	\N	multiple-choice	f	t	0	2018-12-03 16:09:08	2018-12-03 16:09:08	3	\N
478	BE191218-51/52	<p>&nbsp;</p>\n\n<p>Figures A and B ABOVE are the radiographs of a 70-year-old retired man who falls while skiing and injures his right hip. He had no preceding hip pain. After the fall, he is unable to ambulate and is transferred down the mountain by the ski patrol and taken to a hospital<br />\n<br />\n.</p>	multiple-choice	t	f	0	2018-12-03 16:12:28	2018-12-03 16:17:07	3	\N
479	BE191218-53	\N	multiple-choice	f	t	0	2018-12-03 16:17:30	2018-12-03 16:17:30	3	\N
480	BE191218-54/55	<p>Figures ABOVE is a boy 4 years old, his parents notice bowing of right leg since birth. There is no trauma before and he also can walk with crutch and&nbsp; have normal growth. His father has same condition with him, that notice bowing of left leg. This boy have a skin with dark spot of on his body</p>	multiple-choice	t	f	0	2018-12-03 16:19:46	2018-12-03 16:29:57	3	\N
481	BE191218-56	\N	multiple-choice	f	t	0	2018-12-03 16:30:19	2018-12-03 16:30:19	3	\N
482	BE191218-57/58/59	<p>A 10-year-old girl has right knee pain related to activity. An avid soccer player, she has noted pain after the first 15 minutes of running but no swelling or mechanical symptoms. Radiographs show a large 2-cm osteochondritis dissecans (OCD) lesion.</p>	multiple-choice	t	f	0	2018-12-04 18:38:11	2018-12-04 18:42:55	3	\N
483	BE191218-60	\N	multiple-choice	f	t	0	2018-12-04 18:43:37	2018-12-04 18:43:37	3	\N
484	BE191218-61	\N	multiple-choice	f	t	0	2018-12-04 18:48:21	2018-12-04 18:48:21	3	\N
485	BE191218-62	\N	multiple-choice	f	t	0	2018-12-04 18:53:43	2018-12-04 18:53:43	3	\N
486	BE191218-63	\N	multiple-choice	f	t	0	2018-12-04 18:55:18	2018-12-04 18:55:18	3	\N
487	BE191218-64/65/66/67	\N	multiple-choice	t	f	0	2018-12-04 18:57:39	2018-12-04 18:57:39	3	\N
488	BE191218-68/69	\N	multiple-choice	t	f	0	2018-12-06 15:54:33	2018-12-06 15:54:33	3	\N
489	BE191218-70	\N	multiple-choice	f	t	0	2018-12-06 15:59:11	2018-12-06 15:59:11	3	\N
490	BE191218-71/72/73/74	<p>A patient with a history of proximal tibia was hit by a motorcycle 4 hours prior to hospital admission. Primary survey is clear with blood pressure 110/60 mmHg. Clinical picture as shown below, patient had pain on passive stretch of toes. Pulsation of dorsalis pedis artery and tibialis posterior artery are still palpable</p>	multiple-choice	t	f	0	2018-12-06 16:13:46	2018-12-06 16:19:05	3	\N
491	BE191218-75	\N	multiple-choice	f	t	0	2018-12-06 16:20:57	2018-12-06 16:20:57	3	\N
492	BE191218-76	\N	multiple-choice	f	t	0	2018-12-06 16:22:27	2018-12-06 16:22:27	3	\N
493	BE191218-77/78/79	<p>Six month&nbsp;before coming to the clinic, an 80-year-old male sustained an open fracture on his left tibia but refuse any medical treatment. Today the patient come again to the hospital with pus draining&nbsp;from the wound and inability to walk. After thorough evaluation, you diagnose this patient with chronic osteomyelitis and non-union of the left tibia due to neglected untreated open fracture Cierny stage 3 type B</p>	multiple-choice	t	f	0	2018-12-06 16:24:22	2018-12-06 16:28:58	3	\N
494	BE191218-80/81/82/83	<p>A 42-year-old man presents to the hospital with pain and swelling of the dorsum of his hand. He reports blunt trauma against a metal shelf, but does not remember a break in the skin. There is a blister of the skin. He reports erythema started approximately 6 hours ago of the hand but it now extends to the wrist. He is febrile to 102 degrees, heart rate is 110, and blood pressure is 92/38. He has significant pain to palpation and induration of the dorsum of the hand</p>	multiple-choice	t	f	0	2018-12-06 16:29:31	2018-12-06 17:04:55	3	\N
495	BE191218-84	\N	multiple-choice	f	t	0	2018-12-06 17:05:26	2018-12-06 17:05:26	3	\N
496	BE191218-85	\N	multiple-choice	f	t	0	2018-12-06 17:06:56	2018-12-06 17:06:56	3	\N
497	BE191218-86	\N	multiple-choice	f	t	0	2018-12-06 17:09:14	2018-12-06 17:09:14	3	\N
498	BE191218-87	\N	multiple-choice	f	t	0	2018-12-06 17:10:46	2018-12-06 17:10:46	3	\N
499	BE191218-88	\N	multiple-choice	f	t	0	2018-12-11 09:15:39	2018-12-11 09:15:39	3	\N
500	BE191218-89	\N	multiple-choice	f	t	0	2018-12-11 09:17:40	2018-12-11 09:17:40	3	\N
501	BE191218-90	\N	multiple-choice	f	t	0	2018-12-11 09:23:18	2018-12-11 09:23:18	3	\N
502	BE191218-91	\N	multiple-choice	f	t	0	2018-12-11 09:25:02	2018-12-11 09:25:02	3	\N
503	BE191218-92	\N	multiple-choice	f	t	0	2018-12-11 09:31:10	2018-12-11 09:31:10	3	\N
512	BE191218-PED1	<p>A 10 year old boy presented in emergency department after hit by a motorcycle 2 hours before admission. He was unable to move his right lower leg due to pain</p>\n\n<p>The bone fragment on anteromedial was completely detached from soft tissue and could be easily separated off from the remainding bone. Gross meassurement showed that the detached bone fragment is about 50% of the bone circumferential diameter. Neurovascular distal is intact</p>	essay	t	f	0	2018-12-11 13:11:54	2018-12-12 20:56:57	3	\N
513	BE191218-PED2	<p>A 6 year old boy complain for lack of complete elbow flexion as well as limitation of terminal pronation and supinationon of his right elbow. He has a history of painfull and swollen forearm 3 months ago after a fell. The swelling has already been subsided but the limitation of range of motions persist.</p>	essay	t	f	0	2018-12-12 20:57:17	2018-12-12 20:59:14	3	\N
514	BE191218-PED3	<p>A one month old baby was brought to your clinic with bilateral crooked feet. The baby had a normal pregnancy and delivery. The baby is aterm and body weight is 3000 gram. Apgar score was 9/10.</p>	essay	t	f	0	2018-12-12 20:59:32	2018-12-12 21:03:57	3	\N
515	BE191218-PED4	<p>A 5 year old boy came to your clinic with painless, tilted neck since birth. There is no history of cervical trauma or any seizures. The tilted neck was not progressive, the deformity grossly stayed at the same level of severity.&nbsp; The patient can play, run and jump as like the other children of his age. No treatment had been employed before</p>	essay	t	f	0	2018-12-12 21:04:25	2018-12-12 21:08:03	3	\N
516	BE191218-PED5	<p>A four year old boy fell when playing football. He had been limping since 6 month beforehand. There was no history of significant trauma before.</p>	essay	t	f	0	2018-12-12 21:08:26	2018-12-12 21:13:14	3	\N
517	BE191218-MST1	<p>A 15 years-old male complaining lump on his right knee since 2 months ago with moderate pain. He was admitted to hospital for establishing the diagnosis. X-ray, laboratory and biopsy examination was performed two days ago. Hb: 8,0 gr/dl, WB: 9.000 gr/dl, ESR: 40, ALP: 250, LDH: 300</p>	essay	t	f	0	2018-12-17 01:34:42	2018-12-17 02:03:12	3	\N
518	BE191218-MST2	<p>A 50 years-old female with moderate pain on the right arm since 6 months ago. She had a history of breast carcinoma 3 years ago and finished chemo-radiotherapy cycle 2 years ago. She comes to the hospital for follow up check-up and the doctor was found a lesion on her humerus from the x-ray.</p>	essay	t	f	0	2018-12-17 02:04:24	2018-12-17 02:07:59	3	\N
519	BE191218-MST3	<p>A 9 years old boy complain pain the left lower leg for three months. There is no history of trauma. An axial cut of CT scan of lower leg and biopsy result was shown on the figures above</p>	essay	t	f	0	2018-12-17 02:10:03	2018-12-17 02:18:18	3	\N
520	BE191218-MST4	<p>A figure above show pathology on x-ray</p>	essay	t	f	0	2018-12-17 02:19:03	2018-12-17 02:23:40	3	\N
521	BE191218-MST5	<p>A figure above shows an axial cut of thigh MRI</p>	essay	t	f	0	2018-12-17 02:25:55	2018-12-17 02:41:51	3	\N
522	BE191218-AR1	<p>A 40-year-old man sustained an injury to left ankle in a traffic accident. A radiograph was taken upon examination in the emergency department</p>\n\n<div>&nbsp;</div>\n\n<div>&nbsp;</div>\n\n<div>&nbsp;</div>\n\n<div>&nbsp;</div>	essay	t	f	0	2018-12-17 02:42:29	2018-12-17 02:55:25	3	\N
523	BE191218-AR2	<p>A 25-year-old football player sustained a twisting injury to his left knee. He has already had a diagnostic arthroscopy in another hospital. He came to you with an MRI result and a printed intra-operative arthroscopic picture to show you for a second opinion.</p>	essay	t	f	0	2018-12-17 02:55:45	2018-12-17 02:58:39	3	\N
524	BE191218-AR3	<p>A 60-year-old male came with a pain on his right hip. He had a history of prolonged consumption of traditional medicine</p>	essay	t	f	0	2018-12-17 02:59:12	2018-12-17 03:02:45	3	\N
525	BE191218-AR4	<p>A 63-year-old male came with the swollen, warm and painful right knee. Two weeks ago he underwent a knee replacement surgery. He had a fever and initial blood count showed increased ESR (70 mm/h) and leucocyte count (23.000)</p>	essay	t	f	0	2018-12-17 03:03:18	2018-12-17 03:05:51	3	\N
526	BE191218-AR5	<p>A 58-year-old female suddenly felt pain on her left hip after falling from a bed. One year ago she had a total hip replacement surgery. The immediate postoperative (picture A) and the current x-ray (picture B) are shown.</p>	essay	t	f	0	2018-12-17 03:06:13	2018-12-17 03:11:56	3	\N
527	BE191218-32	\N	multiple-choice	f	t	0	2018-12-18 00:50:25	2018-12-18 00:50:25	3	\N
528	BE191218-33	\N	multiple-choice	f	t	0	2018-12-18 00:55:30	2018-12-18 00:56:03	3	\N
529	BE191218-34	\N	multiple-choice	f	t	0	2018-12-18 00:59:11	2018-12-18 00:59:11	3	\N
530	BE29519-MCQ-1	<p>A 34-year-old, right-hand-dominant male is brought to the emergency department by his friends with a chief complaint of right arm pain and deformity. Earlier in the day he was thrown from his dirt bike during an amateur race and attempted to break his fall with his arm. Your examination finds that he is nontender about his elbow but has significant pain and deformity about the forearm and wrist. His radiographs are shown in Figures</p>	multiple-choice	t	f	0	2019-05-21 22:59:10	2020-05-07 20:07:54	3	\N
531	BE29519-MCQ-3	<p>A 37-year-old, male motorcyclist was struck by another vehicle at highway speeds. He was intubated in the field. On arrival to the trauma bay, his GCS is&nbsp; 3-x-3, heart rate is 130 beats per minute, blood pressure is 90/58 mm Hg, lactate is 8, and his base deficit is 6. He is found to have a grade IV splenic laceration, multiple rib fractures, a pelvic ring injury, a closed left femur fracture, a comminuted Gustilo&ndash;Anderson 3C tibia fracture. He is stabilized in the trauma bay and taken directly to the operating room by the general surgery team for exploratory laparotomy</p>	multiple-choice	t	f	0	2019-05-26 08:25:27	2020-05-07 20:08:42	3	\N
532	BE29519-MCQ-6	<p>A 29-year-old woman arrives at the emergency department after jumping from the second floor of a burning building. Upon presentation, she is obtunded and hemodynamically unstable. Her condition, however, stabilizes after transfusion of two units of packed red blood cells and two units of fresh, frozen plasma. She is found to have a subdural hematoma and a splenic laceration, which compels transfer to the intensive care unit for observation. On the secondary survey, significant ecchymosis, swelling, and evolving fracture blisters are noted of the right ankle. The right foot has dopplerable signals over both the posterior tibial and dorsalis pedis arteries. An AP radiograph of the right ankle is shown in Figures.</p>	multiple-choice	t	f	0	2019-05-26 08:32:27	2020-05-07 20:09:39	3	\N
657	BE 131119 MCQ-35	\N	multiple-choice	f	t	0	2019-11-07 09:17:51	2019-11-07 09:17:51	3	\N
658	BE 131119 MCQ-36	\N	multiple-choice	f	t	0	2019-11-07 10:07:58	2019-11-07 10:07:58	3	\N
659	BE 131119 MCQ-37	\N	multiple-choice	f	t	0	2019-11-07 10:39:17	2019-11-07 10:39:17	3	\N
533	BE29519-MCQ-9	<p>A 37-year-old male presents to the emergency department after a fall from 20 ft. Figures below depict an AP radiograph of the patient&rsquo;s right knee. The skin about the knee is intact, however, there is significant ecchymosis and evolving hemorrhagic blisters noted medially. ABI measures 1.1 and this appears to be an isolated injury.</p>	multiple-choice	t	f	0	2019-05-26 08:40:29	2019-05-26 08:45:32	3	\N
534	BE29519-MCQ-12	\N	multiple-choice	f	t	0	2019-05-26 08:45:58	2019-05-26 08:45:58	3	\N
535	BE29519-MCQ-13	\N	multiple-choice	f	t	0	2019-05-26 08:47:56	2019-05-26 08:47:56	3	\N
536	BE29519-MCQ-14	\N	multiple-choice	f	t	0	2019-05-26 08:49:52	2019-05-26 08:49:52	3	\N
537	BE29519-MCQ-15	\N	multiple-choice	f	t	0	2019-05-26 08:53:56	2019-05-26 08:53:56	3	\N
538	BE29519-MCQ-16	\N	multiple-choice	f	t	0	2019-05-26 08:55:22	2019-05-26 08:55:22	3	\N
539	BE29519-MCQ-17	\N	multiple-choice	f	t	0	2019-05-26 08:58:14	2019-05-26 08:58:14	3	\N
540	BE29519-MCQ-19	<p>A 54-year-old female comes to your office with a chief complaint of a painful left palm. When further questioned, she mentioned that she has difficulty moving her finger first thing in the morning and occasionally finds that the finger catches, and she has difficulty opening the palm. Review of systems is negative and the patient reports that she is in otherwise good health. This has been going on the past 6 to 8 weeks.</p>	multiple-choice	t	f	0	2019-05-26 09:00:24	2019-05-26 09:06:01	3	\N
541	BE29519-MCQ-22	\N	multiple-choice	f	t	0	2019-05-26 09:06:28	2019-05-26 09:06:28	3	\N
542	BE29519-MCQ-23	\N	multiple-choice	f	t	0	2019-05-26 09:09:03	2019-05-26 09:09:03	3	\N
543	BE29519-MCQ-24	\N	multiple-choice	f	t	0	2019-05-26 09:12:35	2019-05-26 09:12:35	3	\N
544	BE29519-MCQ-25	\N	multiple-choice	f	t	0	2019-05-26 09:17:10	2019-05-26 09:17:10	3	\N
545	BE29519-MCQ-26	\N	multiple-choice	f	t	0	2019-05-26 09:21:02	2019-05-26 09:21:02	3	\N
546	BE29519-MCQ-27	\N	multiple-choice	f	t	0	2019-05-26 09:23:43	2019-05-26 09:23:43	3	\N
547	BE29519-MCQ-28	\N	multiple-choice	f	t	0	2019-05-26 09:26:22	2019-05-26 09:26:22	3	\N
548	BE29519-MCQ-29	\N	multiple-choice	f	t	0	2019-05-26 09:28:56	2019-05-26 09:28:56	3	\N
549	BE29519-MCQ-30	\N	multiple-choice	f	t	0	2019-05-26 09:31:09	2019-05-26 09:31:09	3	\N
550	BE29519-MCQ-31	\N	multiple-choice	f	t	0	2019-05-26 09:33:00	2019-05-26 09:33:00	3	\N
551	BE29519-MCQ-32	\N	multiple-choice	f	t	0	2019-05-26 09:35:11	2019-05-26 09:35:11	3	\N
552	BE29519-MCQ-33	\N	multiple-choice	f	t	0	2019-05-26 09:37:19	2019-05-26 09:37:19	3	\N
553	BE29519-MCQ-34	\N	multiple-choice	f	t	0	2019-05-26 09:39:20	2019-05-26 09:39:20	3	\N
554	BE29519-MCQ-35	\N	multiple-choice	f	t	0	2019-05-26 09:44:10	2019-05-26 09:44:10	3	\N
555	BE29519-MCQ-36	\N	multiple-choice	f	t	0	2019-05-26 09:46:42	2019-05-26 09:46:45	3	\N
556	BE29519-MCQ-37	<p>A 56-year-old man presents to you with a chief complaint of severe right buttock, posterior thigh, and lower leg pain for 12 weeks. It radiates to the lateral aspect of his foot, and it is worse with sitting or standing for prolonged periods and with walking. Now over the past 2 weeks, he reports difficulty with toe push-off on the right side. Treatment so far has been nonsteroidal anti-inflammatory drugs (NSAID), physical therapy, and an epidural injection without significant relief. Physical examination findings include 4/5 right ankle plantar flexion, a positive straight leg raise on the right, and an absent right Achilles tendon reflex. Images of his lumbar spine are shown in Figures 37A and B.</p>	multiple-choice	t	f	0	2019-05-26 09:51:56	2020-05-07 20:17:39	3	\N
557	BE29519-MCQ-39	<p>You are called to the emergency department to evaluate a 32-year-old man with a history of intravenous drug use who presents with a 2-week history of increasing neck pain and a 2-day history of fevers and progressive weakness in his arms and legs. On examination, he has 3/5 strength globally in his upper and lower extremities and is unable to ambulate without assistance</p>	multiple-choice	t	f	0	2019-05-26 10:16:57	2019-05-26 10:21:16	3	\N
558	BE29519-MCQ-41	<p>A 9-year-old boy presents to your clinic with a limping gait which has been there for a few weeks and the problem has been worsening since then. He takes shorter steps with his left leg and spends less of the stance phase on that limb. Examination of bilateral hips reveals significant limitations in both hips in internal and external rotation and abduction. The x-ray is as follows</p>	multiple-choice	t	f	0	2019-05-26 10:26:48	2020-05-07 20:17:11	3	\N
559	BE29519-MCQ-46	<p>A 16-year-old male came to your emergency department with pain and deformity of his left upper thigh after he was hit by a car while skateboarding earlier that afternoon. His general condition stable. The pelvic x-ray shows proximal femur fracture which then further elaborated with CT scan as follow</p>	multiple-choice	t	f	0	2019-05-26 10:39:00	2020-05-07 20:16:41	3	\N
560	BE29519-MCQ-51	<p>A 6-year-old boy with the complaint of unable to fully flex his left elbow after a fall 4 months ago. At that time his left elbow hit the ground, got swollen and tender. He was brought to the traditional healer by his parents</p>	multiple-choice	t	f	0	2019-05-26 10:47:51	2020-05-07 20:16:22	3	\N
561	BE29519-MCQ-55	<p>A 13-year-old girl with spina bifida has been suffering from crooked right foot since 3 years ago, that is when she started her puberty. The deformity has been progressively worst ever since. Her left foot is doing fine</p>	multiple-choice	t	f	0	2019-05-26 10:58:49	2020-05-07 20:15:56	3	\N
562	BE29519-MCQ-60	<p>A&nbsp;5-year-old boy with a long-standing untreated left clubfoot came to your clinic. He is neurologically intact , normal motoric milestone and active</p>	multiple-choice	t	f	0	2019-05-26 11:08:43	2019-05-26 11:13:46	3	\N
563	BE29519-MCQ-62	\N	multiple-choice	f	t	0	2019-05-26 11:14:15	2019-05-26 11:14:15	3	\N
564	BE29519-MCQ-63	\N	multiple-choice	f	t	0	2019-05-26 11:16:42	2019-05-26 11:16:42	3	\N
565	BE29519-MCQ-64	<p>A pediatrician consulted you for neonates with swelling and deformity on its right femoral region. The x-ray is as follows</p>	multiple-choice	t	f	0	2019-05-26 11:18:18	2019-05-26 11:23:29	3	\N
566	BE29519-MCQ-66	\N	multiple-choice	f	t	0	2019-05-26 11:28:30	2019-05-26 11:28:30	3	\N
567	BE29519-MCQ-67	\N	multiple-choice	f	t	0	2019-05-26 11:33:26	2019-05-26 11:33:26	3	\N
568	BE29519-MCQ-68	\N	multiple-choice	f	t	0	2019-05-26 11:35:21	2019-05-26 11:35:21	3	\N
569	BE29519-MCQ-69	\N	multiple-choice	f	t	0	2019-05-26 11:38:24	2019-05-26 11:38:24	3	\N
570	BE29519-MCQ-70	\N	multiple-choice	f	t	0	2019-05-26 11:47:51	2019-05-26 11:47:51	3	\N
571	BE29519-MCQ-71	\N	multiple-choice	f	t	0	2019-05-26 11:49:33	2019-05-26 11:49:33	3	\N
572	BE29519-MCQ-72	\N	multiple-choice	f	t	0	2019-05-26 11:51:43	2019-05-26 11:51:43	3	\N
573	BE29519-MCQ-73	\N	multiple-choice	f	t	0	2019-05-26 11:53:10	2019-05-26 11:53:10	3	\N
574	BE29519-MCQ-74	\N	multiple-choice	f	t	0	2019-05-26 11:54:44	2019-05-26 11:54:44	3	\N
575	BE29519-MCQ-75	\N	multiple-choice	f	t	0	2019-05-26 11:56:06	2019-05-26 11:56:06	3	\N
576	BE29519-MCQ-76	\N	multiple-choice	f	t	0	2019-05-26 12:03:37	2019-05-26 12:03:37	3	\N
660	BE 131119 MCQ-38	\N	multiple-choice	f	t	0	2019-11-07 11:12:28	2019-11-07 11:12:28	3	\N
661	BE 131119 MCQ-39	\N	multiple-choice	f	t	0	2019-11-07 13:29:04	2019-11-07 13:29:04	3	\N
662	BE 131119 MCQ-40	\N	multiple-choice	f	t	0	2019-11-07 13:31:43	2019-11-07 13:31:43	3	\N
663	BE 131119 MCQ-41	\N	multiple-choice	f	t	0	2019-11-07 13:38:22	2019-11-07 13:40:07	3	\N
666	BE 131119 MCQ-43	\N	multiple-choice	f	t	0	2019-11-07 15:54:56	2019-11-07 15:54:56	3	\N
667	BE 131119 MCQ-44	\N	multiple-choice	f	t	0	2019-11-07 15:58:48	2019-11-07 15:58:48	3	\N
577	BE29519-MCQ-77	<p>The figure above is the radiograph taken 6 weeks ago of a 41-year-old woman with persistent pain in her right leg after sustaining a tibia fracture 12 months ago in a motor vehicle collision. On examination, she has well-healed scars and a well-healed flap on the medial aspect at the level of the fracture. She reports having an infection after the initial surgery, which resulted in debridement of the soft tissue and need for the local rotational flap. There are no changes at the fracture site as shown in the most recent radiograph (Figure 2). She is healthy and has no comorbidities.</p>	multiple-choice	t	f	0	2019-05-26 12:05:30	2019-05-26 12:10:37	3	\N
578	BE29519-MCQ-80	<p>An 18-year-old soccer player injures her knee during a competition. She reports her knee buckled when stepping to kick the ball. She fell to the ground after hearing a pop and was unable to stand on her right leg. Since then, she has been able to bear some weight, but she does not trust her leg. On examination, she has a large swollen knee.</p>	multiple-choice	t	f	0	2019-05-26 12:11:04	2019-05-26 12:15:08	3	\N
579	BE29519-MCQ-83	\N	multiple-choice	f	t	0	2019-05-26 12:25:48	2019-05-26 12:25:48	3	\N
580	BE29519-MCQ-84	\N	multiple-choice	f	t	0	2019-05-26 12:28:30	2019-05-26 12:28:30	3	\N
581	BE29519-MCQ-85	\N	multiple-choice	f	t	0	2019-05-26 12:33:16	2019-05-26 12:33:16	3	\N
582	BE29519-MCQ-86	\N	multiple-choice	f	t	0	2019-05-26 12:42:33	2019-05-26 12:42:38	3	\N
583	BE29519-MCQ-87	\N	multiple-choice	f	t	0	2019-05-26 12:44:39	2019-05-26 12:44:39	3	\N
584	BE29519-MCQ-88	\N	multiple-choice	f	t	0	2019-05-26 12:46:32	2019-05-26 12:46:32	3	\N
585	BE29519-MCQ-89	\N	multiple-choice	f	t	0	2019-05-26 12:52:53	2019-05-26 12:52:53	3	\N
586	BE29519-MCQ-90	\N	multiple-choice	f	t	0	2019-05-26 12:54:54	2019-05-26 12:54:54	3	\N
587	BE29519-MCQ-91	\N	multiple-choice	f	t	0	2019-05-26 12:57:38	2019-05-26 12:57:38	3	\N
588	BE29519-MCQ-92	\N	multiple-choice	f	t	0	2019-05-26 12:59:51	2019-05-26 12:59:51	3	\N
589	BE29519-MCQ-93	\N	multiple-choice	f	t	0	2019-05-26 13:01:37	2019-05-26 13:01:37	3	\N
590	BE29519-MCQ-94	\N	multiple-choice	f	t	0	2019-05-26 13:12:00	2019-05-26 13:12:00	3	\N
591	BE29519-MCQ-95	\N	multiple-choice	f	t	0	2019-05-26 13:15:57	2019-05-26 13:15:57	3	\N
592	BE29519-MCQ-96	\N	multiple-choice	f	t	0	2019-05-26 13:19:33	2019-05-26 13:19:33	3	\N
593	BE29519-MCQ-97	\N	multiple-choice	f	t	0	2019-05-26 13:21:25	2019-05-26 13:21:25	3	\N
594	BE29519-MCQ-98	\N	multiple-choice	f	t	0	2019-05-26 13:39:44	2019-05-26 13:39:44	3	\N
595	BE29519-MCQ-99	\N	multiple-choice	f	t	0	2019-05-26 13:41:35	2019-05-26 13:41:35	3	\N
596	BE29519-MCQ-100	\N	multiple-choice	f	t	0	2019-05-26 13:43:22	2019-05-26 13:43:22	3	\N
597	BE29519-MCQ-18	\N	multiple-choice	f	t	0	2019-05-26 13:56:47	2019-05-26 13:56:47	3	\N
598	BE29519-OSCE-Hand-1	<p>A 25 years old man pain and swelling of his right hand after sustaining a wound prick at index finger while gardening 4 days ago. No history of prior treatment. The patient realizes that swelling spreads to the palm.</p>	essay	t	f	0	2019-05-26 14:15:13	2019-05-26 14:17:44	3	\N
599	BE29519-OSCE-Hand-2	<p>A 56-year-old female presents to the office with pain in the radial sided wrist for several years without a history of trauma. She has noticed that she has difficulty with holding door knobs, carrying heavy plates, and turning the key in her car. The pain keeps her awake at night, and she has tried various anti-inflammatory medications with limited success. The radiograph is shown.</p>	essay	t	f	0	2019-05-26 14:18:17	2019-05-26 14:20:40	3	\N
600	BE29519-OSCE-Hand-3	<p>A 28-year-old woman presents to your office with complaints of pain with full wrist extension. She does not report any antecedent trauma. The patient is tender over the midcarpal. A radiograph is shown.</p>	essay	t	f	0	2019-05-26 14:21:10	2019-05-26 14:22:58	3	\N
601	BE29519-OSCE-Hand-4	<p>A 54-year-old female presents with a hand deformity seen in a clinical picture with history of index finger injury 5 months ago. The patient can&rsquo;t extend her PIP joint.</p>	essay	t	f	0	2019-05-26 14:23:30	2019-05-26 14:25:49	3	\N
602	BE29519-OSCE-Hand-5	<p>A 30-year-old male fell while he was out for a run. He landed outstretched hand on his left thenar eminence sustaining an injury to this area 3 days ago. A radiograph is shown</p>	essay	t	f	0	2019-05-26 14:26:32	2019-05-26 14:28:24	3	\N
603	BE29519-ONCOLOGY-1	<p>A 17-year-old female presented with a lump at the left ankle since 8 months ago. History of trauma (+), Pain (+) goes up and down. Laboratory Findings; Hb: 9,6 g/dl, Leucocytes: 6,000/ml, Thrombocytes: 406.000/ml, ESR: 10, SAP: 304(0-390), and LDH: 218(230-460)<strong>&nbsp;</strong></p>	essay	t	f	0	2019-05-26 15:16:18	2019-05-26 15:20:07	3	\N
604	BE29519-OSCE-ONCOLOGY-2	<p>A 52-year-old female complained pain on the right thigh for 6 months. There is an unclear history of cancer. Laboratory result : ESR :&nbsp; 95&nbsp;&nbsp;&nbsp; SAP : <strong>6</strong><strong>9</strong><strong>8</strong> (&lt; 270 )LDH: <strong>2</strong><strong>1</strong><strong>9</strong> (100-190 )CRP :<strong>38</strong><strong>,2 </strong>( 0 &ndash; 5,0 ).</p>	essay	t	f	0	2019-05-26 15:20:43	2019-05-26 15:23:05	3	\N
605	BE29519-OSCE-ONCOLOGY-3	<p>A 26-year-old male presented with a lump at the right buttock and getting bigger since 4 years ago. Pain (-) and there is no limitation of movement. History of trauma (-), fever(-), loss of appetite (-), and no loss of body weight. Laboratory Findings : Hb : 13,5 g/dl&nbsp;&nbsp;&nbsp; &nbsp;&nbsp;&nbsp; Thrombocytes : 281.000, &nbsp;&nbsp;Leucocytes : 7.200, SAP : 196 (0-270), and LDH : 585 (100-190</p>	essay	t	f	0	2019-05-26 15:23:38	2019-05-26 15:25:40	3	\N
606	BE29519-OSCE-ONCOLOGY-4	<p>A 20 years old male complained lump and deformity on both thighs since&nbsp; 4 months.. Laboratory result:&nbsp; ESR :&nbsp;&nbsp; 80&nbsp;&nbsp; SAP : <strong>5</strong><strong>43</strong> (&lt; 270 ) LDH: <strong>27</strong><strong>8</strong> (100-190 )&nbsp; CRP :<strong>21</strong><strong>,2</strong> ( 0 &ndash; 5,0 ).</p>	essay	t	f	0	2019-05-26 15:26:06	2019-05-26 15:28:25	3	\N
607	BE29519-OSCE-ONCOLOGY-5	<p>A 12 years old female visit your clinic with lump and pain in her right knee since 3 months ago. Lump increase in size very fast and she unable to walk since 1,5 month ago. Her parents show you the x-ray and laboratory result.</p>\n\n<table border="1" cellspacing="0" style="width:308.4pt">\n\t<tbody>\n\t\t<tr>\n\t\t\t<td style="vertical-align:top; width:172.55pt">\n\t\t\t<p><strong>Items</strong></p>\n\t\t\t</td>\n\t\t\t<td style="vertical-align:top; width:70.4pt">\n\t\t\t<p><strong>Result</strong></p>\n\t\t\t</td>\n\t\t\t<td style="vertical-align:top; width:65.45pt">\n\t\t\t<p><strong>Parameter</strong></p>\n\t\t\t</td>\n\t\t</tr>\n\t\t<tr>\n\t\t\t<td style="vertical-align:top; width:172.55pt">\n\t\t\t<p>Hb</p>\n\t\t\t</td>\n\t\t\t<td style="vertical-align:top; width:70.4pt">\n\t\t\t<p>9,0</p>\n\t\t\t</td>\n\t\t\t<td style="vertical-align:top; width:65.45pt">\n\t\t\t<p>12 - 14</p>\n\t\t\t</td>\n\t\t</tr>\n\t\t<tr>\n\t\t\t<td style="vertical-align:top; width:172.55pt">\n\t\t\t<p>WBC</p>\n\t\t\t</td>\n\t\t\t<td style="vertical-align:top; width:70.4pt">\n\t\t\t<p>8000</p>\n\t\t\t</td>\n\t\t\t<td style="vertical-align:top; width:65.45pt">\n\t\t\t<p>&nbsp;</p>\n\t\t\t</td>\n\t\t</tr>\n\t\t<tr>\n\t\t\t<td style="vertical-align:top; width:172.55pt">\n\t\t\t<p>ESR</p>\n\t\t\t</td>\n\t\t\t<td style="vertical-align:top; width:70.4pt">\n\t\t\t<p>50</p>\n\t\t\t</td>\n\t\t\t<td style="vertical-align:top; width:65.45pt">\n\t\t\t<p>20-40</p>\n\t\t\t</td>\n\t\t</tr>\n\t\t<tr>\n\t\t\t<td style="vertical-align:top; width:172.55pt">\n\t\t\t<p>CRP</p>\n\t\t\t</td>\n\t\t\t<td style="vertical-align:top; width:70.4pt">\n\t\t\t<p>1,2</p>\n\t\t\t</td>\n\t\t\t<td style="vertical-align:top; width:65.45pt">\n\t\t\t<p>&lt; 1</p>\n\t\t\t</td>\n\t\t</tr>\n\t\t<tr>\n\t\t\t<td style="vertical-align:top; width:172.55pt">\n\t\t\t<p>Alkaline Phosphatase (ALP)</p>\n\t\t\t</td>\n\t\t\t<td style="vertical-align:top; width:70.4pt">\n\t\t\t<p>400</p>\n\t\t\t</td>\n\t\t\t<td style="vertical-align:top; width:65.45pt">\n\t\t\t<p>&lt;270</p>\n\t\t\t</td>\n\t\t</tr>\n\t\t<tr>\n\t\t\t<td style="vertical-align:top; width:172.55pt">\n\t\t\t<p>Lactate Dehydrogenase (LDH)</p>\n\t\t\t</td>\n\t\t\t<td style="vertical-align:top; width:70.4pt">\n\t\t\t<p>350</p>\n\t\t\t</td>\n\t\t\t<td style="vertical-align:top; width:65.45pt">\n\t\t\t<p>100-190</p>\n\t\t\t</td>\n\t\t</tr>\n\t</tbody>\n</table>	essay	t	f	0	2019-05-26 15:28:56	2019-05-26 15:30:50	3	\N
608	BE29519-OSCE-AR-1	<p>Based on these figures</p>	essay	t	f	0	2019-05-27 16:16:48	2019-05-27 16:20:03	3	\N
609	BE29519-OSCE-AR-2	<p>A 28-year-old basketball player suffered from a head-on collision while driving his car three months ago. One thing that he remembered was that the collision was so hard so that the dashboard of his car directly hit his leg. Now he feels that his left knee is painful especially when he runs and jumps vigorously.</p>	essay	t	f	0	2019-05-27 16:20:59	2019-05-27 16:25:16	3	\N
665	BE 131119 MCQ-42	\N	multiple-choice	f	t	0	2019-11-07 15:05:05	2019-11-07 15:05:05	3	\N
668	BE 131119 MCQ-45	\N	multiple-choice	f	t	0	2019-11-07 16:12:01	2019-11-07 16:12:01	3	\N
610	BE29519-OSCE-AR-3	<p>A 35-year-old female came to you with a chief complaint of inability to walk due to pain in her left lower leg. Ten months prior to her visit to your hospital, she was involved in a road traffic accident and had an open fracture (Gustilo-Anderson grade IIIB) of the left tibia (AO42.C3) (see Figure 1). She was sent to a rural hospital for debridement and external fixation. Now she came with failed external fixation and pus-producing wound. Another x-ray and laboratory examination have been done in your hospital.</p>	essay	t	f	0	2019-05-27 16:25:48	2019-05-27 16:28:36	3	\N
611	BE29519-OSCE-AR-4	<p>A 30-year-old male came with a painful and deformed left hip after a road traffic accident. The hip was shortened, adducted, and internally rotated. A closed reduction was performed in the emergency room.</p>	essay	t	f	0	2019-05-27 16:29:15	2019-05-27 16:31:32	3	\N
612	BE29519-OSCE-AR-5	<p>A 45-year-old housewife came to you with a painful left knee at the inner side, especially while walking. No pain was elicited when she climbed stairs. The patient had received numerous treatment, medication, and physical therapy but to no avail. A standing knee x-ray was done.</p>	essay	t	f	0	2019-05-27 16:32:03	2019-05-27 16:34:51	3	\N
613	BE29519-OSCE-Ped-1	<p>A 15 years old boy complaining pain on right hip since 3,5 months ago. There is no history of fever or trauma. ESR 60 mm/hour.</p>	essay	t	f	0	2019-05-27 16:35:19	2019-05-27 16:50:46	3	\N
614	BE29519-OSCE-Ped-2	<p>A 10 years old girl with a deformity on her right elbow since 4 years ago. She went to the traditional bone setter for her elbow injury. At this moment, she cannot flex the elbow and feel that her left elbow is unstable.</p>	essay	t	f	0	2019-05-27 16:51:40	2019-05-27 16:57:36	3	\N
615	BE29519-OSCE-Ped-3	<p>A 6 months old boy with a complaint of prolonged on and off a fever for the last week accompanied with a tender, hyperemic soft tissue mass on distal of her left femur. She had an acute upper respiratory tract infection 2 weeks ago</p>	essay	t	f	0	2019-05-27 16:56:41	2019-05-27 16:59:58	3	\N
616	BE29519-OSCE-Ped-4	<p>A 4 months old baby with bilateral clubfoot</p>	essay	t	f	0	2019-05-27 17:00:44	2019-05-27 17:02:53	3	\N
617	BE29519-OSCE-Ped-5	<p>A 2-month-old male without other known medical problems is referred for left arm deformity. There is an absence of a thumb, severe radial deviation of the digits, and carpus in relation to the forearm. This deviation is passively correctable to within 20 degrees of neutral. His forearm is shortened. He demonstrates good active flexion and extension of the digits and responds to noxious stimuli throughout the hand.</p>	essay	t	f	0	2019-05-27 17:03:29	2019-05-27 17:09:36	3	\N
618	BE29519-OSCE-Spine-1	<p>These are the&nbsp; CT scans of a 40-year-old man.&nbsp; He had severe back pain for 4 weeks.&nbsp; The history of fever since a week ago and have no weakness and numbness in his lower extremities with normal reflex of the Patellar and Achilles tendon reflex.</p>	essay	t	f	0	2019-05-27 17:48:56	2019-05-27 17:51:52	3	\N
619	BE29519-OSCE-Spine-2	<p>This is an x-ray of a 58-year-old woman with severe back pain for the past 4 months.&nbsp; He has numbness and weakness in knee extension.&nbsp; She had a history of right radical mastectomy 2 years ago.</p>	essay	t	f	0	2019-05-27 17:52:20	2019-05-27 17:54:15	3	\N
620	BE29519-OSCE-Spine-3	<p>This is MRI&nbsp; of a 32-year-old man with left radicular pain for the past 4 weeks (VAS 6/10).&nbsp; He has slight weakness in extension of the great toe (4/5) and numbness at the dorsal skin left foot. Nonoperative treatment has been prescribed 2 weeks ago&nbsp; with significant improvement</p>	essay	t	f	0	2019-05-27 17:54:47	2019-05-27 17:57:00	3	\N
621	BE29519-OSCE-Spine-4	<p>This is CT scan a 70-year-old woman who fell at home. She has severe&nbsp; back pain (VAS: 7/10) especially in changing of position, but the motor and sensory functions are normal</p>	essay	t	f	0	2019-05-27 17:57:26	2019-05-27 17:59:15	3	\N
622	BE29519-OSCE-Spine-5	<p>A young female, 14 years old, came to the outpatient clinic presented with a hump on her back just a couple years ago. None of the family members noticed until she felt uncomfort every time she wears dresses.</p>\n\n<p>After x-ray measurements, Cobb Angle Erect/Bending. Proximal Thoracic T1 &ndash; T5 (5 / 0); Main Thoracic T5 &ndash; T12 (62 / 41); Lumbar T12 &ndash; L4 (28 / 10).&nbsp;&nbsp; Lumbar modifier B. Thoracic Sagittal Profile 30.&nbsp; Risser sign 3</p>	essay	t	f	0	2019-05-27 17:59:36	2019-05-27 18:02:00	3	\N
623	BE 131119- MCQ1	\N	multiple-choice	f	t	0	2019-11-05 10:25:10	2019-11-05 11:03:46	3	\N
624	BE 131119-MCQ-2	\N	multiple-choice	f	t	0	2019-11-05 10:36:43	2019-11-05 11:04:16	3	\N
625	BE 131119-MCQ-3	\N	multiple-choice	f	t	0	2019-11-05 10:46:14	2019-11-05 11:05:08	3	\N
626	BE 131119 MCQ-4	\N	multiple-choice	f	t	0	2019-11-05 11:00:05	2019-11-05 11:05:43	3	\N
627	BE 131119 MCQ-5	\N	multiple-choice	f	t	0	2019-11-05 11:09:10	2019-11-05 11:09:10	3	\N
628	BE 131119 MCQ-6	\N	multiple-choice	f	t	0	2019-11-05 11:13:30	2019-11-05 11:13:30	3	\N
629	BE 131119 MCQ-7	\N	multiple-choice	f	t	0	2019-11-05 11:17:00	2019-11-05 11:17:00	3	\N
630	BE 131119 MCQ-8	\N	multiple-choice	f	t	0	2019-11-05 11:20:33	2019-11-05 11:22:16	3	\N
631	BE 131119 MCQ-9	\N	multiple-choice	f	t	0	2019-11-05 11:24:29	2019-11-05 11:24:29	3	\N
633	BE 131119 MCQ-11	\N	multiple-choice	f	t	0	2019-11-07 07:35:22	2019-11-07 07:35:22	3	\N
634	BE 131119 MCQ-12	\N	multiple-choice	f	t	0	2019-11-07 07:38:59	2019-11-07 07:38:59	3	\N
635	BE 131119 MCQ-13	\N	multiple-choice	f	t	0	2019-11-07 07:41:34	2019-11-07 07:41:34	3	\N
636	BE 131119 MCQ-14	\N	multiple-choice	f	t	0	2019-11-07 07:46:27	2019-11-07 07:46:27	3	\N
637	BE 131119 MCQ-15	\N	multiple-choice	f	t	0	2019-11-07 07:48:34	2019-11-07 07:48:34	3	\N
638	BE 131119 MCQ-16	\N	multiple-choice	f	t	0	2019-11-07 07:58:15	2019-11-07 07:58:15	3	\N
639	BE 131119 MCQ-17	\N	multiple-choice	f	t	0	2019-11-07 08:00:33	2019-11-07 08:00:33	3	\N
640	BE 131119 MCQ-18	\N	multiple-choice	f	t	0	2019-11-07 08:02:49	2019-11-07 08:02:49	3	\N
641	BE 131119 MCQ-19	\N	multiple-choice	f	t	0	2019-11-07 08:09:35	2019-11-07 08:09:35	3	\N
642	BE 131119 MCQ-20	\N	multiple-choice	f	t	0	2019-11-07 08:16:32	2019-11-07 08:16:32	3	\N
643	BE 131119 MCQ-21	\N	multiple-choice	f	t	0	2019-11-07 08:18:37	2019-11-07 08:18:37	3	\N
644	BE 131119 MCQ-22	\N	multiple-choice	f	t	0	2019-11-07 08:22:24	2019-11-07 08:22:24	3	\N
645	BE 131119 MCQ-23	\N	multiple-choice	f	t	0	2019-11-07 08:26:42	2019-11-07 08:26:42	3	\N
646	BE 131119 MCQ-24	\N	multiple-choice	f	t	0	2019-11-07 08:30:11	2019-11-07 08:30:11	3	\N
647	BE 131119 MCQ-25	\N	multiple-choice	f	t	0	2019-11-07 08:32:17	2019-11-07 08:32:17	3	\N
648	BE 131119 MCQ-26	\N	multiple-choice	f	t	0	2019-11-07 08:35:57	2019-11-07 08:35:57	3	\N
649	BE 131119 MCQ-27	\N	multiple-choice	f	t	0	2019-11-07 08:39:16	2019-11-07 08:39:16	3	\N
650	BE 131119 MCQ-28	\N	multiple-choice	f	t	0	2019-11-07 08:41:35	2019-11-07 08:41:35	3	\N
651	BE 131119 MCQ-29	\N	multiple-choice	f	t	0	2019-11-07 08:47:02	2019-11-07 08:47:02	3	\N
652	BE 131119 MCQ-30	\N	multiple-choice	f	t	0	2019-11-07 08:50:33	2019-11-07 08:50:33	3	\N
653	BE 131119 MCQ-31	\N	multiple-choice	f	t	0	2019-11-07 09:01:45	2019-11-07 09:01:45	3	\N
654	BE 131119 MCQ-52	\N	multiple-choice	f	t	0	2019-11-07 09:04:46	2019-11-07 09:04:46	3	\N
655	BE 131119 MCQ-33	\N	multiple-choice	f	t	0	2019-11-07 09:09:38	2019-11-07 09:09:38	3	\N
656	BE 131119 MCQ-34	\N	multiple-choice	f	t	0	2019-11-07 09:13:40	2019-11-07 09:13:40	3	\N
669	BE 131119 MCQ-46	\N	multiple-choice	f	t	0	2019-11-07 16:43:02	2019-11-07 16:43:02	3	\N
670	BE 131119 MCQ-47	\N	multiple-choice	f	t	0	2019-11-07 16:50:25	2019-11-07 16:50:25	3	\N
671	BE 131119 MCQ-48	\N	multiple-choice	f	t	0	2019-11-07 16:55:38	2019-11-07 16:55:38	3	\N
672	BE 131119 MCQ-49	\N	multiple-choice	f	t	0	2019-11-07 17:00:25	2019-11-07 17:00:25	3	\N
673	BE 131119 MCQ-50	\N	multiple-choice	f	t	0	2019-11-07 17:10:38	2019-11-07 17:10:38	3	\N
674	BE 131119 MCQ-51	\N	multiple-choice	f	t	0	2019-11-07 18:06:31	2019-11-07 18:06:31	3	\N
675	BE 131119 MCQ-52	\N	multiple-choice	f	t	0	2019-11-07 18:13:21	2019-11-07 18:13:21	3	\N
676	BE 131119 MCQ-53	\N	multiple-choice	f	t	0	2019-11-07 18:34:23	2019-11-07 18:34:23	3	\N
677	BE 131119 MCQ-54	\N	multiple-choice	f	t	0	2019-11-07 18:36:57	2019-11-07 18:36:57	3	\N
678	BE 131119 MCQ-55	\N	multiple-choice	f	t	0	2019-11-07 18:40:30	2019-11-07 18:40:30	3	\N
679	BE 131119 MCQ-56	\N	multiple-choice	f	t	0	2019-11-07 18:42:03	2019-11-07 18:42:03	3	\N
680	BE 131119 MCQ-57	\N	multiple-choice	f	t	0	2019-11-07 18:44:18	2019-11-07 18:44:18	3	\N
681	BE 131119 MCQ-58	\N	multiple-choice	f	t	0	2019-11-07 18:52:08	2019-11-07 18:52:08	3	\N
682	BE 131119 MCQ-59	\N	multiple-choice	f	t	0	2019-11-07 18:58:11	2019-11-07 18:58:11	3	\N
683	BE 131119 MCQ-60	\N	multiple-choice	f	t	0	2019-11-07 19:03:27	2019-11-07 19:03:27	3	\N
684	BE 131119 MCQ-61	<p>A 42-years-old gentleman suffered from acute back pain for the past two weeks. The vital signs are within normal range, with sub febrile (T : 37,9 Celsius), He denies numbness and weakness.&nbsp; CT scan results are shown below.</p>	multiple-choice	t	f	0	2019-11-07 19:06:38	2020-05-07 20:04:31	3	\N
685	BE 131119 MCQ-64	<p>This is the sagittal MRI scan of a 34-year old man, who suffer from severe low back pain for 2 months. Standing, sitting, forward bending will exacerbate the pain, while rest in supine position will reduce the pain.</p>	multiple-choice	t	f	0	2019-11-07 19:19:06	2020-05-07 20:04:55	3	\N
686	BE 131119 MCQ-67	<p>A 44-year old-man come to A&amp;E Department suffered from total tetraplegia after a motor vehicle collision 3 days ago. Physical findings BP: 80 /50&nbsp; , heart rate : 40 time/minute, RR : 26/minute, temperature : 36,7 Celsius</p>	multiple-choice	t	f	0	2019-11-07 19:51:54	2020-05-07 20:05:07	3	\N
687	BE 131119 MCQ-70	<p>The current right femur lateral radiograph of a 9-year-old boy who went to the emergency department after falling from his skateboard. He has acute right leg pain, deformity, and cannot bear weight. Vascular and neurologic examination findings are normal. His skin is intact; however, he has a healed 3-inch scar on the lateral side of his right thigh. The boy weighs 40 kg.</p>	multiple-choice	t	f	0	2019-11-08 07:57:57	2019-11-08 08:02:19	3	\N
688	BE 131119 MCQ-72	<p>A 10-year-old boy&nbsp; has right knee pain related to activity for the last 3 months. An avid soccer player, he has noted pain on the front part of the knee after the first 15 minutes of running. There was swelling but without any mechanical symptoms</p>	multiple-choice	t	f	0	2019-11-08 08:05:16	2019-11-08 08:11:09	3	\N
689	BE 131119 MCQ-75	<p>A 13-year-old and overweight boy came with the complain of left hip pain that sometimes radiates to the knee. The pain has been there for 6 months, on and off. Physical examination reveals that he is still able to walk with a Trendelenburg gait of the left hip with limited internal rotation and abduction due to pain. X ray of the left hip as follows</p>	multiple-choice	t	f	0	2019-11-08 08:12:52	2020-05-07 20:05:24	3	\N
690	BE 131119 MCQ-78	<p>A 9 year old boy with a history of swollen and painfull forearm after a fell down 3 months ago. The patient were brought to a local bonesetter where his forearm is splinter for 6 weeks. After the splint is removed, the forearm is pain free however there is limitation of movement on his elbow. What is your clinical diagnosis</p>	multiple-choice	t	f	0	2019-11-08 08:19:07	2020-05-07 20:05:41	3	\N
691	BE 131119 MCQ-80	<p>A 14 months old girl came to your clinic with a short-leg gait of the left lower extremity. History shows that she was born with breech presentation. There is no history of pain or fever. The trendelenburg sign on the left hip is positive with limited abduction</p>	multiple-choice	t	f	0	2019-11-08 08:27:48	2020-05-07 20:05:56	3	\N
692	BE 131119 MCQ-82	<p>A 76-year-old, right-hand-dominant man presents to clinic complaining of right shoulder pain. The pain started several months ago, has gotten progressively worse, and is located diffusely over his deltoid region. He has night pain and pain with overhead activity. On examination, there is no visible muscle atrophy, and he has full passive and near full active range of motion. He experiences pain and some weakness with resisted shoulder forward flexion and abduction.</p>	multiple-choice	t	f	0	2019-11-08 08:32:47	2020-05-07 20:03:55	3	\N
693	BE 131119 MCQ-85	<p>A 9-year-old boy presents to your clinic with a limping gait which has been there for a few weeks and the problem has been worsening since then. He takes shorter steps with his left leg and spends less of the stance phase on that limb. Examination of bilateral hips reveals significant limitations in both hips in internal and external rotation and abduction. The x ray is as follows</p>	multiple-choice	t	f	0	2019-11-08 08:41:11	2020-05-07 20:03:35	3	\N
695	BE 131119 MCQ-90	\N	multiple-choice	f	t	0	2019-11-08 08:51:19	2019-11-08 08:51:19	3	\N
697	BE 131119 MCQ-92	\N	multiple-choice	f	t	0	2019-11-08 08:58:46	2019-11-08 08:58:46	3	\N
699	BE 131119 MCQ-94	\N	multiple-choice	f	t	0	2019-11-08 09:10:36	2019-11-08 09:10:36	3	\N
700	BE 131119 MCQ-95	\N	multiple-choice	f	t	0	2019-11-08 09:12:47	2019-11-08 09:12:47	3	\N
701	BE 131119 MCQ-96	\N	multiple-choice	f	t	0	2019-11-08 09:15:26	2019-11-08 09:15:26	3	\N
702	BE 131119 MCQ-97	\N	multiple-choice	f	t	0	2019-11-08 09:17:36	2019-11-08 09:17:36	3	\N
704	BE 131119 MCQ-99	\N	multiple-choice	f	t	0	2019-11-08 09:24:14	2019-11-08 09:24:14	3	\N
705	BE 131119 MCQ-100	\N	multiple-choice	f	t	0	2019-11-08 09:27:37	2019-11-08 09:27:37	3	\N
706	BE 131119 MCQ-10	\N	multiple-choice	f	t	0	2019-11-08 09:45:39	2019-11-08 09:45:39	3	\N
707	BE 131119 OSCE HAND 1	<p>Based on this anatomical pictures. Please answer these following questions.</p>	essay	t	f	0	2019-11-08 14:33:26	2020-05-07 21:09:11	3	\N
708	BE 131119 OSCE HAND 2	<p>This is the lateral x-ray view of male 35 years old, injured his hand while playing basketball and present to the emergency room.</p>	essay	t	f	0	2019-11-08 14:37:23	2020-05-07 21:09:27	3	\N
709	BE 131119 OSCE HAND 3	<p>A 28-year-old, right-hand-dominant male caught big air going off a jump while playing soccer for the first time. He landed awkwardly on his non-dominant left hand and immediately developed pain.</p>	essay	t	f	0	2019-11-08 14:40:41	2020-05-07 21:10:12	3	\N
710	BE 131119 OSCE HAND 4	<p>55-year-old female who presents with 5 days of left sided long finger pain.&nbsp; She denies a history of injury to the finger.&nbsp; She was seen at an urgent care and started on warm soaks, elevation, and oral 1st generation cephalosporin, but the pain and swelling has persisted.&nbsp;</p>	essay	t	f	0	2019-11-08 14:43:46	2020-05-07 21:10:40	3	\N
711	BE 131119 OSCE HAND 5	<p>Figure above&nbsp;show a child with a congenital abnormality.</p>	essay	t	f	0	2019-11-08 14:47:33	2020-05-07 21:12:00	3	\N
712	BE 131119 OSCE MST1	<p>Male, 35 years old, complained lump on the proximal leg for 3 months. Laboratory result : ESR :&nbsp;&nbsp; 80, SAP : <strong>599</strong> (&lt; 270 &nbsp;LDH: <strong>279</strong> (100-190 )&nbsp; CRP :<strong>49,2</strong> ( 0 &ndash; 5,0 ). Clinical, radiological and Histopathological pictures shown below</p>	essay	t	f	0	2019-11-12 15:17:14	2019-11-12 15:20:48	3	\N
713	BE 131119 OSCE MST2	<p>Male, 20 years old complained lump and deformity on both thighs for 4 months. Laboratory result:&nbsp; ESR :&nbsp;&nbsp; 80&nbsp;&nbsp; SAP : <strong>5</strong><strong>43</strong> (&lt; 270 ) LDH: <strong>27</strong><strong>8</strong> (100-190 )&nbsp; CRP :<strong>21</strong><strong>,2</strong> ( 0 &ndash; 5,0 ). The radiology and histopathology results were shown below.</p>	essay	t	f	0	2019-11-12 15:21:40	2019-11-12 15:27:41	3	\N
714	BE 131119 OSCE MST-3	<p>Male, 34 years old complained about incontinent urine and alvi since 5&nbsp;months. There is no history of trauma. Laboratory result : ESR :&nbsp; 80&nbsp;&nbsp;&nbsp; SAP : <strong>5</strong><strong>23</strong> (&lt; 270 )&nbsp; LDH: <strong>2</strong><strong>80</strong> (100-190 )&nbsp;&nbsp;&nbsp; CRP :<strong>49,2</strong> ( 0 &ndash; 5,0 ).&nbsp; Chest X-ray: No sign Metastasis and the Bone scan revealed that single lesion. Pelvic x-ray and histopathology result as shown below</p>	essay	t	f	0	2019-11-12 15:28:26	2019-11-12 15:35:32	3	\N
715	BE 131119 OSCE MST-4	<p>A 55 years old female complaining severe pain on her left arm suddenly in the morning. No history of trauma and cancer before. She went to an emergency room and admitted to the hospital for further management.</p>	essay	t	f	0	2019-11-12 15:36:31	2019-11-12 15:58:02	3	\N
716	BE 131119 OSCE MST-5	<p>45 years old female admitted to hospital due to severe pain on her left hip. She was unable to walk after wake up in the morning. She had a history of mastectomy 3 years ago and completed serial chemotherapy 2 years ago. The X-ray picture was shown below.</p>	essay	t	f	0	2019-11-12 15:41:21	2019-11-12 15:44:17	3	\N
717	BE 131119 OSCE SPINE-1	<p>This is a T2 weighted image of a cervical MRI&nbsp; of a 32-year-old man, who suffered from complete tetraplegia after a road traffic accident.</p>	essay	t	f	0	2019-11-12 15:48:44	2019-11-12 15:51:58	3	\N
718	BE 131119 OSCE SPINE-2	<p>72-year-old woman has gait and balance difficulties since 3 months ago. Physical examination reveals a weakness in upper extremity weakness (3/5) and lower extremity (4/5). The MRI result is shown below :</p>	essay	t	f	0	2019-11-12 15:53:12	2019-11-12 15:57:48	3	\N
719	BE 131119 OSCE SPINE-3	<p>Spinal physiology :</p>\n\n<p>&nbsp;</p>\n\n<ol>\n\t<li>&nbsp;</li>\n</ol>	essay	t	f	0	2019-11-12 15:58:38	2019-11-12 16:01:13	3	\N
720	BE 131119 OSCE SPINE-4	<p>A 55-year-old man with diabetes mellitus.&nbsp; He had back pain for 5 months.&nbsp; He now has a fever and progressive weakness and numbness in his lower extremities.&nbsp; Examination reveals 3/5 strength in both lower extremities, with decreased sensation in both lower extremities. Lab result : Hb 10,6 , WBC : 11.000 LED : 60/90</p>\n\n<p>The MRI scan is shown below:</p>	essay	t	f	0	2019-11-12 16:01:40	2019-11-12 16:05:16	3	\N
721	BE 131119 OSCE SPINE-5	<p>These are the result of post-contrast fluoroscopy on the lumbar region of a 46-year-old gentleman who has had low-back pain for 3 months.&nbsp; He also has pain on the right buttock, but no numbness or weakness reveals on both lower legs.&nbsp; His pain is worsened by extension and relieved with flexion.</p>	essay	t	f	0	2019-11-12 16:05:41	2019-11-12 16:09:24	3	\N
722	BE 131119 OSCE PED-1	<p>The figure above shows the Ponseti method</p>	essay	t	f	0	2019-11-12 16:10:07	2019-11-12 16:14:41	3	\N
37	What measure of physiologic status	\N	multiple-choice	f	f	0	2017-07-10 00:33:05	2017-07-10 00:33:05	3	VbBavkw5
38	An adult with distal humeral fracture	\N	multiple-choice	f	t	0	2017-07-10 00:35:38	2017-12-27 23:44:58	3	wJkqYkOY
44	Late surgical treatment of posttraumatic	\N	multiple-choice	f	t	0	2017-07-10 00:47:48	2017-12-27 23:50:56	3	DjBrjlB2
51	A 13-year-old boy who plays multiple sports has had insidious-onset heel	\N	multiple-choice	f	f	0	2017-07-10 00:58:55	2017-12-27 23:43:20	3	prK2LNkn
61	Regarding vertebroplasty in spinal osteoporotic fracture	\N	multiple-choice	f	f	0	2017-07-10 01:18:25	2017-07-10 01:18:25	3	nJg7vDkl
71	After total hip arthroplasty (THA)	\N	multiple-choice	f	f	0	2017-07-10 01:41:25	2017-07-10 01:41:25	3	wdKvy1g2
76	A newborn with bilateral talipes equinovarus undergoes	\N	multiple-choice	f	f	0	2017-07-10 01:57:05	2017-07-10 01:57:05	3	JMBbEmKp
81	A 30-year -old, right-hand-dominant	<p>A 30-year -old, right-hand-dominant man presents to the clinic complaining of anterior right shoulder pain. There is pain mostly with an overhead movement that radiates to the biceps muscle belly. He takes no medications, is otherwise healthy, and works as a car mechanic. He is an avid volleyball player. His examination includes a positive Hawkins test, positive Yerguson&#39;s test, tenderness to palpation over the intertubercular sulcus, and a negative Speed&#39;s test. The rest of the examination is normal. Plain radiographs are normal.</p>	multiple-choice	t	f	0	2017-07-10 02:08:25	2018-07-09 23:48:51	3	VbBaPvKw
85	A 43-year -old man was struck by a car	<p>A 43-year -old man was struck by a car while walking along the road. Neurologic examination demonstrates 5/5 strength in all muscle groups in his upper extremities but 0/5 strength throughout the lower extremities. Though sensation throughout the lower extremities is absent, he has diminished yet present perianal sensation to light touch and pinprick. His imaging studies are shown in figures above.</p>\n\n<p>In the trauma bay, the patient &#39;s blood pressure suddenly drops to 80/50 mm Hg while his pulse increases to 120 bpm. One liter fluid bolus of lactated Ringers is infused which normalizes his blood pressure and pulse.</p>	multiple-choice	t	f	0	2017-07-10 02:38:02	2017-12-27 23:52:18	3	xDkVbAkX
110	Adult recon 3	<p>A 30-y.o healthy man who sustained &nbsp;an anterior R-shoulder dislocation while playing baseball. He requires a closed reduction under sedation at a local EMG dept. He is placed into a shoulder immobilizer and referred to your office for further treatment. Upon inquiry, the patient states that he previously dislocated the shoulder twice within the last year while playing. &nbsp;X-ray result is normal. He demonstrates positive apprehension and speed tests.</p>	essay	t	f	0	2017-07-10 14:39:10	2017-07-10 14:54:17	3	z8KwQLko
113	Adult recon 1	<p>A 15-year-old-girl complaining knee pain. This condition was happen more than three times with trivial injury. Physical examination found knock knees and hyperextention of both elbow and knee.</p>	essay	t	f	0	2017-07-10 14:46:04	2017-07-10 14:53:00	3	3ZBYQqB0
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	2014_10_12_000000_create_users_table	1
2	2014_10_12_100000_create_password_resets_table	1
3	2014_10_12_200000_add_two_factor_columns_to_users_table	1
4	2015_01_15_105324_create_roles_table	1
5	2015_01_15_114412_create_role_user_table	1
6	2015_01_26_115212_create_permissions_table	1
7	2015_01_26_115523_create_permission_role_table	1
8	2015_02_09_132439_create_permission_user_table	1
9	2017_05_16_194927_create_exams_table	1
10	2017_06_03_223233_create_items_table	1
11	2017_06_03_223236_create_questions_table	1
12	2017_06_03_224300_create_answers_table	1
13	2017_06_03_224745_create_exam_item_table	1
14	2017_06_03_225542_create_categories_table	1
15	2017_06_03_225634_create_category_item_table	1
16	2017_06_03_225634_create_category_question_table	1
17	2017_06_04_050156_create_takers_table	1
18	2017_06_04_050317_create_groups_table	1
19	2017_06_04_051142_create_group_taker_table	1
20	2017_06_18_013609_create_deliveries_table	1
21	2017_06_18_014256_create_delivery_taker_table	1
22	2017_06_18_014509_create_attempts_table	1
23	2017_06_18_014955_create_attempt_question_table	1
24	2017_07_04_150707_create_attachments_table	1
25	2017_07_04_150942_create_attachables_table	1
26	2017_07_12_054111_create_activity_log_table	1
27	2019_08_19_000000_create_failed_jobs_table	1
28	2019_12_14_000001_create_personal_access_tokens_table	1
29	2022_01_18_182401_create_sessions_table	1
30	2022_08_06_005016_create_register_data_table	1
31	2023_02_03_193655_add_finish_scoring_to_attempts_table	1
32	2023_05_08_141045_add_column_to_delivery_taker_table	1
33	2023_06_26_010847_alter_table_exams_add_column_is_mcq	1
34	2023_06_26_022653_alter_table_groups_add_closed_at	1
35	2023_07_06_161356_alter_attempt_question_table	1
36	2023_09_22_145802_alter_table_exams_add_is_interview	1
37	2025_08_29_155644_create_clients_table	1
38	2025_08_29_155756_add_client_id_to_multiple_tables	1
39	2025_08_29_155909_add_system_fields_to_roles_table	1
40	2025_08_29_161248_reset_users_table_sequence	1
41	2025_08_29_161413_reset_role_user_table_sequence	1
42	2025_08_29_202700_add_is_published_to_exams_table	2
43	2025_08_29_213158_add_finished_at_to_attempts_table	2
44	2025_08_29_214403_add_title_to_exams_table	2
45	2025_08_29_add_logo_to_clients_table	2
46	2025_08_30_060542_add_client_id_to_roles_table	2
47	2025_08_30_070843_fix_username_unique_constraint_for_multi_tenant	2
48	2025_08_30_072041_remove_duplicate_reg_unique_constraint_from_takers	2
49	2025_09_02_231138_add_hash_columns_to_tables	2
50	2025_09_15_165435_add_hash_to_remaining_tables	2
51	2025_10_31_003024_create_delivery_snapshots_table	2
52	2025_11_05_000001_create_exam_session_logs_table	3
53	2025_11_05_000002_add_admin_fields_to_users_table	3
\.


--
-- Data for Name: password_resets; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_resets (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: permission_role; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.permission_role (id, permission_id, role_id, granted, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: permission_user; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.permission_user (id, permission_id, user_id, granted, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: permissions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.permissions (id, name, slug, description, model, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: questions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.questions (id, item_id, type, question, is_random, score, "order", created_at, updated_at, client_id, hash) FROM stdin;
12	11	multiple-choice	<p>A 12,5 year-old-boy reports intermittent knee pain and limping that interferes with his ability to participating in sport. He actively participates in football, basketball, and baseball. He denies any history of injury. Examination shows a full range of motion without effusion. Radiographs reveal an Osteochondritis dissecans (OCD) lesion on the lateral aspect of the medial femoral condyle. MRI scans are shows in the figure above. &nbsp;Initial treatment should consist of:</p>	f	100	0	2017-07-09 22:38:18	2017-07-09 22:38:18	3	e0c46d0a88d251b9c34183f20fdc8dfa
13	12	multiple-choice	<p>The figure above shows the radiograph of a 2-year-old child with marked genu varum and tibial bowing. Based on these findings, what is the best initial course of action?</p>	f	100	0	2017-07-09 22:41:52	2017-07-09 22:41:52	3	8618b06546185f8234e68a8bb2691c7c
14	13	multiple-choice	<p>A 6-year-old boy with acute hematogenous osteomyelitis of the distal femur is being treated with intravenous antibiotics. The most expeditious method to determine the early success or failure of treatment is by serial evaluation of which of the following studies?</p>	f	100	0	2017-07-09 23:02:09	2017-07-09 23:02:09	3	f87816a172f7259c07351f95a54aa391
15	14	multiple-choice	<p>A nonambulatory verbal 6-year-old child with spastic quadriplegic cerebral palsy has a progressive bilateral hip subluxation of more than 50%. There is no pain with range of motion, but abduction is limited to 20 degrees maximum. &nbsp;An AP radiograph is seen above. Management should consist of:</p>	f	100	0	2017-07-09 23:04:07	2017-07-09 23:04:07	3	5767c76402695b5a0abddf40d320284f
16	15	multiple-choice	<p>A 37-year-old man pulled his hamstring playing softball 3 weeks ago. The patient had not noted any mass prior to his injury. MRI scans of the posterior thigh and biopsy specimen from a needle biopsy are shown in figures above. What is the most likely diagnosis?</p>	f	100	0	2017-07-09 23:10:21	2017-07-09 23:10:21	3	53ddeecf205b1c78996ae6e432c797c5
17	16	multiple-choice	<p>A 13-year-old boy has a painless &quot;knot&quot; over his left hip. History reveals that he injured his left hip playing soccer 4 months ago. A radiograph and MRI scan obtained at the time of injury are shown in the figure above. He is very active and is currently asymptomatic. A current radiograph is also shown in figures above. What is the next most appropriate step in management?</p>	f	100	0	2017-07-09 23:12:10	2017-07-09 23:12:10	3	07296f091b80b8fe37828b9409aa8d01
18	17	multiple-choice	<p>A 17-year-old girl who initially presented during childhood with multiple skeletal lesion, cafe-au-lait spots, and precocious puberty now has bone pain. &nbsp;A recent bone scan reveals multiple areas of increased scintigraphic uptake, including bilateral proximal femurs. A radiograph is shown in the figure above. &nbsp;In addition to activity modification, what is the best next line of treatment for decreasing her pain?</p>	f	100	0	2017-07-09 23:16:01	2017-07-09 23:16:01	3	d3356f9573fc57f87997a7d2bcd1770b
19	18	multiple-choice	<p>What are the four most common soft-tissue sarcomas to spread via lymph node system?</p>	f	100	0	2017-07-09 23:18:40	2017-07-09 23:18:40	3	91676b278fe02e14633ce905bc9eb5e1
20	19	multiple-choice	<p>A 13-year-old boy has knee pain after sustaining a mild twisting injury while playing basketball 4 weeks ago. Radiographs, MRI scans, and histology sections are shown in figures above. Treatment should consist of:</p>	f	100	0	2017-07-09 23:20:14	2017-07-09 23:20:14	3	37938f7cef85c2edd92cc409f66c7d2e
21	20	multiple-choice	<p>A 64-year-old man has had increasing pain in the left hip for the past 6 months. A radiograph, MRI scan, and biopsy specimens are shown in figures above. What is the recommended treatment?</p>	f	100	0	2017-07-09 23:27:00	2017-07-09 23:27:00	3	f401a8ec012b45fa8bf4420c80d6910e
22	21	multiple-choice	<p>Figures above show the radiograph and MRI scan of a 22-year-old man with knee pain. What is the most likely diagnosis?</p>	f	100	0	2017-07-09 23:40:25	2017-07-09 23:40:25	3	f77cdddb19b964c626e36c174c499b43
23	22	multiple-choice	<p>Which of the following is the most associated with local recurrence of the lesion seen in the radiograph and MRI scan shown in figures above?</p>	f	100	0	2017-07-09 23:44:14	2017-07-09 23:44:14	3	c3adf759103cb03477476a412ce5c9fe
24	23	multiple-choice	<p>A 33-year-old woman reports a mass on the right hand that has been enlarging for 1 year. An intraoperative photograph and histology section is shown in figures above. What is the most likely diagnosis?</p>	f	100	0	2017-07-09 23:47:37	2017-07-09 23:47:37	3	b6c3323b21fc2ff7df0f7d77595fc498
25	24	multiple-choice	<p>A 15-year-old girl had a painful mass on the medial aspect of her thigh for the past 5 years. The pain is present only when she is performing athletic activities and is completely relieved with rest. &nbsp;A radiograph and MRI scan are shown in figures above. The patient and her parents would like to have the mass removed. What is the further diagnostic studies are required prior to considering surgical resection?</p>	f	100	0	2017-07-09 23:52:13	2017-07-09 23:52:13	3	4f8f4eda2125f24b15ec5a15d263db4d
26	25	multiple-choice	<p>In patients with displaced radial neck fractures treated with open reduction and internal fixation with a plate and screws, the plate must be limited to what surface of the radius to avoid impingement on proximal ulna?</p>	f	100	0	2017-07-09 23:54:30	2017-07-09 23:54:30	3	8d525846ea4a4c586d6f8926744fdaf6
27	26	multiple-choice	<p>When harvesting an iliac crest bone graft from a posterior approach, what anatomic structures are at greatest risk for injury if a Cobb elevator is directed too caudal?</p>	f	100	0	2017-07-09 23:55:50	2017-07-09 23:55:50	3	0941e1c7ec06462d36851354997f8bc4
28	27	multiple-choice	<p>A 36-year-old woman sustained a tarsometatarsal joint fracture-dislocation in a motor vehicle accident. The patient is treated with open reduction and internal fixation. What is the most common complication?</p>	f	100	0	2017-07-09 23:57:16	2017-07-09 23:57:16	3	395125d25a9c0df52b8a0681b4394877
29	28	multiple-choice	<p>What is the most appropriate indication for replantation in an otherwise healthy 35-year-old man?</p>	f	100	0	2017-07-09 23:58:48	2017-07-09 23:58:48	3	843da1ac1166ac329e7b91e63bfb2707
30	29	multiple-choice	<p>A 46-year-old man fell 20 feet and sustained the injury shown in the figure above. The injury is closed; however, the soft tissue is swollen and ecchymotic with blisters. The most appropriate initial management should consist of:</p>	f	100	0	2017-07-10 00:00:34	2017-07-10 00:00:34	3	0201b9345fe5857832c95728d9bffc08
31	30	multiple-choice	<p>A 20-year-old man sustained a closed tibial fracture and is treated with a reamed intramedullary nail. What is the most common complication associated with this treatment?</p>	f	100	0	2017-07-10 00:02:01	2017-07-10 00:02:01	3	c4febc225786632acfe4013eb3551133
128	96	essay	<p>How do you manage surgically&nbsp; according to your diagnosis (35)</p>	f	100	0	2017-07-10 12:31:15	2017-07-10 12:31:15	3	f67e63ab5830a7be7435064bb88253ee
32	31	multiple-choice	<p>An active 49-year-old woman who sustained a diaphyseal fracture of the clavicle 8 months ago now reports persistent shoulder pain with daily activities. An AP radiograph is shown in the figure above. Management should consist of:</p>	f	100	0	2017-07-10 00:03:31	2017-07-10 00:03:31	3	72bfa4e665ab9ab94a41b968a1ff7a73
33	32	multiple-choice	<p>Examination of a 25-year-old man who was injured in a motor vehicle accident reveals a fracture dislocation of C5-6 with a Frankel B spinal cord injury. He also has a closed femoral shaft fracture and a grade II open ipsilateral midshaft tibial fracture. Assessment of his vital signs reveals a pulse rate of 45/ min, blood pressure of 80/45 mmHg, and respiration of 25/min. A general surgeon has assessed the abdomen and peritoneal lavage results are negative. His clinical presentation is most consistent with what type of shock?</p>	f	100	0	2017-07-10 00:05:00	2017-07-10 00:05:00	3	22f2c974a5fa8549cdf4aa7c31834b4b
34	33	multiple-choice	<p>Figures above shown the initial radiograph of an 18-year-old man who fell while snowboarding. He was treated with a closed reduction that shown in figures above also. Examination reveals that the elbow is stable with a range of motion. Management should consist of:</p>	f	100	0	2017-07-10 00:06:36	2017-07-10 00:06:36	3	eccb6aa460b5a1192d2183011045fb85
35	34	multiple-choice	<p>Which is the following is an advantage of un-reamed nailing of the tibia compared to reamed nailing?</p>	f	100	0	2017-07-10 00:09:48	2017-07-10 00:09:48	3	ffb2a211584ad136e892ed76c2a8e76d
36	35	multiple-choice	<p>What is the major difference in outcome following open reduction and internal fixation (ORIF) of the tibial plafond at 2 to 5 days versus 10-20 days?</p>	f	100	0	2017-07-10 00:11:19	2017-07-10 00:11:19	3	75fe46d39c51bb3b1942526ffef6eeea
37	36	multiple-choice	<p>The figure above shows the radiograph pf an elderly man who fell on his right arm. What is the most important determinate of a good outcome following this injury?</p>	f	100	0	2017-07-10 00:13:05	2017-07-10 00:31:26	3	78001dcad25ebd0f85adbe8823dd3424
38	37	multiple-choice	<p>What measure of physiologic status best evaluates whether an injured patient is fully resuscitated and best predicts that perioperative complication will be minimized following definitive stabilization of long bone fractures?</p>	f	100	0	2017-07-10 00:35:05	2017-07-10 00:35:05	3	15b35de770defb891fa0f5e22619405b
39	38	multiple-choice	<p>An adult with distal humeral fracture underwent open reduction and internal fixation. What is the most common preoperative complication?</p>	f	100	0	2017-07-10 00:37:06	2017-07-10 00:37:06	3	ff7c7e723d7f1281bfc206fbf27315b6
40	39	multiple-choice	<p>A 35-year-old man is brought to the emergency department following a motorcycle accident. He is breathing spontaneously and has a systolic blood pressure of 80 mmHg. a pulse rate of 120/min, and a temperature of 37 Celcius degrees. Examination suggests an unstable pelvic fracture; AP radiographs confirm an open book injury with vertical displacement on the left side. Ultrasound evaluation of the abdomen is negative. Despite administration of 4 L of normal saline solution, he still has a systolic pressure of 90 mmHg and a pulse rate of 110. Urine output has been about 20 mL, since arrival 35 minutes ago. What is the best next course of action?</p>	f	100	0	2017-07-10 00:39:06	2017-07-10 00:39:06	3	194741fab5de3ef7d81c3bcdbcf97e23
41	40	multiple-choice	<p>A 42-year-old woman sustained a closed talar neck fracture in a motor vehicle accident. Which of the following is an avoidable complication of surgical treatment?</p>	f	100	0	2017-07-10 00:41:16	2017-07-10 00:41:16	3	34b531a75495a1d13edc67928a9cdb25
42	41	multiple-choice	<p>A 25-year-old man is brought to the emergency department following a motor vehicle accident. Extrication time was 2 hours, and in the field, he had a systolic blood pressure by palpation of 90 mmHg. Intravenous therapy was started, and on arrival to the emergency department his systolic blood pressure is 90 mmHg with a pulse rate of 130. Examination reveals a flail chest and a femoral diaphyseal fracture. Ultrasound of the abdomen is positive. The trauma surgeons take him to the operating room for an exploratory laparotomy. At the conclusion of the procedure, systolic pressure of 100 mmHg with a pulse rate of 110. Oxygen saturation is 90% on 100% oxygen, and the patient&#39;s temperature is 35 Celcius degrees. What is the recommended treatment of the femoral fracture at this time?</p>	f	100	0	2017-07-10 00:42:52	2017-07-10 00:42:52	3	6035b7cd9f276227652c5de77293d305
43	42	multiple-choice	<p>A 9-year-old child sustains a proximal tibial physeal fracture with a hyperextension mechanism. What structure is at most risk for serious injury?</p>	f	100	0	2017-07-10 00:44:22	2017-07-10 00:44:22	3	a1f5726be21307b637c8f128432c9beb
93	84	multiple-choice	<p>The most likely complication after this fracture is likely to be which of the following?</p>	f	100	0	2017-07-10 02:37:32	2017-07-10 02:37:32	3	05e2245ecc49c3999736b3981d65b098
44	43	multiple-choice	<p>An 8-year-old girl has treated for a Salter-harris typeI fracture of the right distal femur 2 years ago. Examination reveals symmetric knee flexion, extension, and frontal alignment compared to the contralateral knee. She has 1 cm shortening of the right femur. History reveals that she has always been in the 50th percentile for height, and her skeletal age matches her chronological age. Radiograph is shown in the figure above. What is the expected consequence at maturity?</p>	f	100	0	2017-07-10 00:45:56	2017-07-10 00:45:56	3	e2d2c7d21bfbf3f674b9f98dd38e9803
45	44	multiple-choice	<p>Late surgical treatment of posttraumatic cubitus varus (gunstock deformity) is usually necessitated by the patient reporting problems related to:</p>	f	100	0	2017-07-10 00:48:51	2017-07-10 00:48:51	3	490235aa5528c2131a38b5f4a25af3a1
46	45	multiple-choice	<p>In obstetric brachial plexus palsy, which of the following signs are associated with the poorer prognosis for recovery in a 2-month-old-infant?</p>	f	100	0	2017-07-10 00:50:39	2017-07-10 00:50:39	3	76f97d1b7c68d0ea7748712a25859877
47	46	multiple-choice	<p>Split posterior tibial tendon transfer is used in the treatment of children with cerebral palsy. Which of the following patients is considered the most appropriate candidate for this procedure?</p>	f	100	0	2017-07-10 00:52:08	2017-07-10 00:52:08	3	7dac3915167cf8ad02b386f86203f7ba
48	47	multiple-choice	<p>A 72-year-old male is complaining of dull pain on both of his palms and tip of fingers. Physical examination shows weakness on pinching. The most likely diagnosis will be:</p>	f	100	0	2017-07-10 00:53:23	2017-07-10 00:53:23	3	4894d0b20ddd08c7bc38bb8248f3af35
49	48	multiple-choice	<p>A 3-year-old boy was brought by his mother with the concern of tilting neck. Physical examination shows plagiocephaly and fibrotic muscle on his left neck. The patient was born with a big birthweight. X-ray shows a 22-degree lateral curve with the apex at C5. Clinical diagnosis of this patient is:</p>	f	100	0	2017-07-10 00:54:55	2017-07-10 00:54:55	3	8a1d84d64a9d98568453b3ade7f7da24
50	49	multiple-choice	<p>An overweight &nbsp;74-year-old lady is complaining about low back pain which also radiates until both of her sole feet. The pain gets worse if she walks for more than 100 meters. Physical examination show step-off on her low back at the level of L5. Your working diagnosis is?</p>	f	100	0	2017-07-10 00:56:39	2017-07-10 00:56:39	3	5966ab853d02dfcd0b39f4c80fe08cb1
992	521	essay	<p>In what compartment is the lesion? (25)</p>	f	100	0	2018-12-17 02:41:51	2018-12-19 08:12:30	3	3c83469f0c520a70662d990bdb33e8c0
994	522	essay	<p>Please describe the x-ray and mention one classification of this fracture! (30)</p>	f	100	0	2018-12-17 02:55:25	2018-12-17 02:55:25	3	37d14c28bfccb80558fa27c0da969f61
51	50	multiple-choice	<p>A 30-year-old man reports pain and weakness in his right arm. Examination reveals grade 4 strength in wrist flexion and elbow extension, decreased sensation over the middle finger, and decreased triceps reflex. These symptoms are most compatible with impingement on what spinal nerve root?</p>	f	100	0	2017-07-10 00:58:41	2017-07-10 00:58:41	3	531b3e4f25f8445e3a8dfc51591f7194
52	51	multiple-choice	<p>A 13-year-old boy who plays multiple sports has had insidious-onset heel pain while running for 3 months. On examination, he had ankle dorsiflexion of 5 degrees. The squeeze test result was positive and the Thompson test result was negative. He has no pain with forced ankle plantar flexion. What is the most likely diagnosis?</p>	f	100	0	2017-07-10 01:00:03	2017-07-10 01:00:03	3	cc95de6b8f86f4a0cdce917394339876
132	97	essay	<div>Describe the terms of open biopsy technique in musculoskeletal tumors(35)</div>	f	100	0	2017-07-10 12:40:55	2017-07-10 12:40:55	3	17d9d0ae4d5515280fa83194d33f3042
53	53	multiple-choice	<p>A 26-year-old man reports a 2-week history of a burning pain along the dorso-radial aspect of the distal forearm. The pain radiates to the dorsum of the thumb. Examination reveals tenderness and reproduction of symptoms with percussion 8 cm proximal to the radial styloid. Reproduction of symptoms also occurs with forearm pronation and ulnar deviation of the wrist. No discrete sensory deficit is noted and electrodiagnostic studies are normal. Nonsurgical management consisting of rest, splinting, and anti-inflammatory medications for 6 weeks has failed to provide relief. Treatment should now consist of decompression of the:</p>	f	100	0	2017-07-10 01:01:33	2017-07-10 01:01:33	3	688a769757f8ed6ad26e0d421839b3ee
54	54	multiple-choice	<p>An 18-year-old man sustained a knife injury to his mid-back, with the entry wound 2 cm to the left of the midline. Hemicord transection has been diagnosed. Neurologic examination will most likely reveal left sided loss of</p>	f	100	0	2017-07-10 01:03:07	2017-07-10 01:03:07	3	2016cfddc19acf7c38559f2490482d37
55	55	multiple-choice	<p>In thoracolumbar fracture, which is of the radiographic findings that indicates failure of middle column:</p>	f	100	0	2017-07-10 01:04:23	2017-07-10 01:04:23	3	585f5f6eddf6cbf6982bdc2d7abd4efa
56	56	multiple-choice	<p>Flexion injury in thoracolumbar :</p>	f	100	0	2017-07-10 01:05:42	2017-07-10 01:05:42	3	b0af5805013c87a02d4a63625550dfa7
57	57	multiple-choice	<p>Vertebral compression fracture in osteoporotic spine:</p>	f	100	0	2017-07-10 01:06:54	2017-07-10 01:06:54	3	cea4c69ca60e76f50528e8a214755511
58	58	multiple-choice	<p>Which is the statement is <strong>TRUE</strong> in spondyloarthropathies :</p>	f	100	0	2017-07-10 01:09:06	2017-07-10 01:09:06	3	cdc1ee6610f3da7b37e315b5f9b73efb
59	59	multiple-choice	<p>Which is the statement is <strong>TRUE</strong> in ankylosing spondylitis:</p>	f	100	0	2017-07-10 01:15:37	2017-07-10 01:15:37	3	7a1588a9507d9dc4bd1c87882cab9e0a
60	60	multiple-choice	<p>Absolute indication for discectomy in disc herniation is:</p>	f	100	0	2017-07-10 01:17:09	2017-07-10 01:17:09	3	74d46826a67f93552830ac35667b22e1
61	61	multiple-choice	<p>Regarding vertebroplasty in spinal osteoporotic fracture, which is the TRUE statement:</p>	f	100	0	2017-07-10 01:19:39	2017-07-10 01:19:39	3	ef8ca1e3c4f140d4e70a1fc78595b68b
62	62	multiple-choice	<p>Neurogenic claudication lumbar spinal stenosis:</p>	f	100	0	2017-07-10 01:22:36	2017-07-10 01:22:36	3	06de5adf601c196c16980efc37600c52
63	63	multiple-choice	<p>Which is the special test to establish the herniated disc:</p>	f	100	0	2017-07-10 01:24:00	2017-07-10 01:24:00	3	a3f3ae242dbaeaa71a01ceae8750eb21
64	64	multiple-choice	<p>A healthy, active 72-year-old man tripped and fell, landing on his left hip 10 weeks after an uncomplicated left primary uncemented total hip replacement. A radiograph taken 6 weeks after surgery and before the fall is shown in Figure 10a. A radiograph taken after the fall is shown in Figure 10b. He was unable to bear weight and was brought to the emergency department. Examination revealed a slightly shortened left lower extremity and some mild ecchymosis just distal to the left greater trochanteric region, but his skin was intact without abrasions or lacerations. What is the most appropriate treatment?</p>	f	100	0	2017-07-10 01:26:55	2017-07-10 01:26:55	3	005dedd342086e9fa1905a4fc6badc2c
65	65	multiple-choice	<p>A 70-year-old woman who underwent total knee replacement 18 months ago has had 3 weeks of moderate drainage from a previously healed wound. What is the most appropriate treatment?</p>	f	100	0	2017-07-10 01:29:20	2017-07-10 01:29:20	3	eb2054e49f9633c7c887442e04972072
94	85	multiple-choice	<p>This clinical phenomenon is best characterized as which of the following?</p>	f	100	0	2017-07-10 02:41:23	2017-07-10 02:41:23	3	f80dace61c79be59426025fd64fe7acf
66	66	multiple-choice	<p>A 67-year-old man who underwent an uncomplicated hip arthroplasty 9 years ago has had a 1-week history of groin pain with movement. Radiographs reveal a well-positioned, well- fixed cementless arthroplasty with mild eccentricity of the femoral head within the polyethylene. His serum C-reactive protein (CRP) level is 3.0 mg/L (reference range, 0.08-3.1 mg/L) and erythrocyte sedimentation rate (ESR) is 5 mm/h (reference range, 0-20 mm/h). What is the most appropriate next step in management of the patient?</p>	f	100	0	2017-07-10 01:31:05	2017-07-10 01:31:05	3	1aff632e2768525dc542b738a14f64af
67	67	multiple-choice	<p>Figures above are the radiographs of a 25-year-old woman whose pain has progressed during the last several years to pain with any activity and pain at night. What is the most appropriate treatment?</p>	f	100	0	2017-07-10 01:34:18	2017-07-10 01:34:18	3	55f819d45bb4b00eb6547737ed96a036
68	68	multiple-choice	<p>An orthopaedic surgeon noticed a displaced calcar fracture during stem insertion when performing total hip arthroplasty using cementless fixation. What is the most appropriate course of action?</p>	f	100	0	2017-07-10 01:36:15	2017-07-10 01:36:15	3	611ae42dd87a73905d5ca6f701ac0c54
69	69	multiple-choice	<p>A 59-year-old active woman underwent elective total hip replacement using a posterior approach. She had minimal pain and was discharged to home 2 days after surgery. Four weeks later she dislocated her hip while shaving her legs. She underwent a closed reduction in the emergency department. Postreduction radiographs show a reduced hip with well-fixed components in satisfactory alignment. What is the most appropriate management of this condition from this point forward?</p>	f	100	0	2017-07-10 01:38:19	2017-07-10 01:38:19	3	b36f932b2f749030538768a61f8dba82
70	70	multiple-choice	<p>What factor is associated with a higher risk for dislocation after total hip arthroplasty?</p>	f	100	0	2017-07-10 01:40:52	2017-07-10 01:40:52	3	583688281b9761beb64a158a6c519498
71	71	multiple-choice	<p>After total hip arthroplasty (THA) for osteoarthritis a patient is unable to dorsiflex her ankle or extend her great toe. She is treated conservatively and after 3 months on physical therapy she ambulates with a &quot;slapping gait.&quot; What is the most appropriate next treatment option?</p>	f	100	0	2017-07-10 01:48:20	2017-07-10 01:48:20	3	02255eb25d6f0352e8d836fecdd10623
995	522	essay	<p>What is the further investigation? What is the purpose? (20)</p>	f	100	0	2018-12-17 02:55:25	2018-12-17 02:55:25	3	59db920c482d22e821d879a37b021c9c
72	72	multiple-choice	<p>Which of the following has been shown to increase the rate of failure of cemented femoral components in total hip arthroplasty?</p>	f	100	0	2017-07-10 01:49:49	2017-07-10 01:49:49	3	2bb848f78a134db958ebe8b6b503312f
73	73	multiple-choice	<p>A 40-year-old female sustains the injury seen in Figure Above. What other associated soft-tissue knee injury is most commonly associated with this fracture?</p>	f	100	0	2017-07-10 01:52:02	2017-07-10 01:52:02	3	dc93a24bc89e96acc9c4b7969757f8f0
74	74	multiple-choice	<p>A 34-year-old male presents with the right posterior wall acetabular fracture shown in Figure Above. What is the most accurate method to test for hip stability in this patient?</p>	f	100	0	2017-07-10 01:55:46	2017-07-10 01:55:46	3	f0340bbb3f32a7a6508b7f3a85713fbe
75	75	multiple-choice	<p>Which is the following nerve roots is at risk during anterior placement of the iliosacral screw in treatment of sacroiliac disruption?</p>	f	100	0	2017-07-10 01:56:49	2017-07-10 01:56:49	3	4572f66cfdd5893ed837e4a6902fb530
76	76	multiple-choice	<p>A newborn with bilateral talipes equinovarus undergoes serial manipulation and casting. What is the primary goal of manipulation?</p>	f	100	0	2017-07-10 01:58:11	2017-07-10 01:58:11	3	60444d43e965514fc49d890cbbab8964
77	77	multiple-choice	<p>The statements bellow are right for fracture healing <strong>EXCEPT</strong> :</p>	f	100	0	2017-07-10 01:59:30	2017-07-10 01:59:30	3	0d57b5e3c744d90a600d1a8d9d01ba69
78	78	multiple-choice	<p>A 3-year-old boy has a rigid 40-degree lumbar scoliosis that is the result of a fully segmented L5 hemivertebra. All other examination findings are normal. Management should consist of:</p>	f	100	0	2017-07-10 02:00:51	2017-07-10 02:00:51	3	9648d06cb1272f0a7e9c6aadcc0dea4b
79	79	multiple-choice	<p>In a patient with vertebral tuberculosis, which of the following characteristics is most predictive of progression of the kyphosis?</p>	f	100	0	2017-07-10 02:01:27	2017-07-10 02:01:27	3	56a5d0f9c38de742a87e267eec765e8f
133	97	essay	<div>Describe HUVOS score(35)</div>	f	100	0	2017-07-10 12:40:55	2017-07-10 12:40:55	3	86d12bb20d47d78f1be9f305e285c0b9
134	97	essay	<div>What the different between immunohistopathology and immunocytology (30)</div>	f	100	0	2017-07-10 12:40:55	2017-07-11 21:36:01	3	b94f611d755abc93a84af373510ecc12
191	115	essay	<p>List 3 Indication for operation? (25)</p>	f	100	0	2017-07-10 14:50:48	2017-07-10 14:50:48	3	61c0a26887fc4fbe684452cff8181841
80	80	multiple-choice	<p>A patient is seen shortly after birth for evaluation of bilateral rigid clubfoot deformities. &nbsp;The child lacks passive and active wrist extension. &nbsp;Elbow range of motion is from full extension to 30 degrees of flexion with a fixed pronation deformity. &nbsp;The shoulders, hips, and knees have full range of motion. &nbsp;&nbsp;The most likely diagnosis is:</p>	f	100	0	2017-07-10 02:03:21	2017-07-10 02:03:21	3	67f0fbe0aa3f4223959121df170b3c31
81	81	multiple-choice	<p>Which of the following is not a physical examination finding in biceps tendon pathology?</p>	f	100	0	2017-07-10 02:12:46	2017-07-10 02:12:46	3	f5793c4af9126a92bb8dba754ef7fd7f
82	81	multiple-choice	<p>If the above patient is clinically diagnosed with biceps tendinitis, what is the preferred initial management?</p>	f	100	0	2017-07-10 02:12:46	2017-07-10 02:45:29	3	fc9a34482b0d0713cf506bc3de5ef1da
83	81	multiple-choice	<p>Which of the following is not an indication for surgical intervention with long head of the biceps tendon pathology?</p>	f	100	0	2017-07-10 02:12:46	2017-07-10 02:12:46	3	55e4933b1dd3c2f3a56d700ebdee6687
85	82	multiple-choice	<p>Where does this tendon principally insert?</p>	f	100	0	2017-07-10 02:21:10	2017-07-10 02:21:10	3	b52d847c9a7de3c055df2d345dcbfa01
86	82	multiple-choice	<p>What is the anatomical peculiarity of the ECU?</p>	f	100	0	2017-07-10 02:21:10	2017-07-10 02:21:10	3	4eb6cc5c931f3d7a7d0e362e26a09182
87	82	multiple-choice	<p>In addition to acting as a wrist extensor, what other role has been attributed to the ECU?</p>	f	100	0	2017-07-10 02:21:10	2017-07-10 02:21:10	3	a4ffc10e3b3a8ae0d10e166fd566f301
88	83	multiple-choice	<p>Based on this history and radiographic examination, how should you advise the family?</p>	f	100	0	2017-07-10 02:24:03	2017-07-10 02:24:03	3	6c87de0dc751957ab805f52cb748e903
89	83	multiple-choice	<p>What is the most likely underlying bone problem?</p>	f	100	0	2017-07-10 02:26:51	2017-07-10 02:26:51	3	8350fa6c66b9f18ac48d1dc440cd31eb
90	83	multiple-choice	<p>The fracture location and pattern can be explained because</p>	f	100	0	2017-07-10 02:26:51	2017-07-10 02:26:51	3	f5f6983d9fd202b9a97bd154fdf30dae
91	84	multiple-choice	<p>The most appropriate treatment at this time would be which of the following?</p>	f	100	0	2017-07-10 02:37:32	2017-07-10 02:37:32	3	ea3de35de4668086b2881f943a686adc
691	377	essay	<div>What is your diagnosis ? (30)</div>	f	100	0	2018-07-14 09:24:15	2018-07-14 21:23:14	3	e1c6c8f15bb0241fe11f71a8f65305ea
95	85	multiple-choice	<p>During early management and resuscitation of this patient, which of the following is currently recommended in order to maximize neurological recovery?</p>	f	100	0	2017-07-10 02:41:23	2017-07-10 02:41:23	3	1666d0458106f290e475a742e4122611
96	85	multiple-choice	<p>Definitive management of this patient&#39;s spinal injury should be:</p>	f	100	0	2017-07-10 02:41:23	2017-07-10 02:41:23	3	317e29a13f4d5a960153c484027feb4e
97	86	multiple-choice	<p>What is the most likely diagnosis?</p>	f	100	0	2017-07-10 02:44:27	2017-07-10 02:44:27	3	c87127e77d16482235291b23828ef768
98	86	multiple-choice	<p>What is the treatment of choice ?</p>	f	100	0	2017-07-10 02:44:27	2017-07-10 02:44:27	3	4ba394ce8ae15b43d291e1a30b8715b9
99	87	multiple-choice	<p>What is the most likely diagnosis:</p>	f	100	0	2017-07-10 02:47:12	2017-07-10 02:47:12	3	5de79969e351f5ffaff80a473f5ac168
100	87	multiple-choice	<p>What is the best treatmen option:</p>	f	100	0	2017-07-10 02:47:12	2017-07-10 02:47:12	3	3e0198597b91407810fa0d8d279f5ced
101	88	multiple-choice	<p>What is the recommended treatment?</p>	f	100	0	2017-07-10 02:52:42	2017-07-10 02:52:42	3	09982521c3a4199f89e790aadbcf782d
102	88	multiple-choice	<p>What purpose does the Southwick&rsquo;s angle in the evaluation of this pathology?</p>	f	100	0	2017-07-10 02:52:42	2017-07-10 02:52:42	3	08dd188191c970efc2bef84cd0046d16
103	88	multiple-choice	<p>If AP radiographs of the pelvis does not provide adequate information, what would the next step of investigation be?</p>	f	100	0	2017-07-10 02:52:42	2017-07-10 02:52:42	3	fe4c575053157b574c22d750ed57f1c5
104	89	multiple-choice	<p>Based on this patient&rsquo;s history and examination, what is the best next step?</p>	f	100	0	2017-07-10 02:56:16	2017-07-10 02:56:16	3	d1898b135995d65dc5c83aab0d8fb509
105	89	multiple-choice	<p>Blood culture reveal methicillin-sensitive Staphylococcus aureus (MSSA). The patient&rsquo;s &nbsp;examination remains unchanged. In addition to outpatient serial laboratory studies and weekly observation for neurologic deterioration, which of the following is the most appropriate for nonoperative treatment?</p>	f	100	0	2017-07-10 02:56:16	2017-07-10 02:56:16	3	ff01baaa9b4266c00b83eb4b7a2fa624
106	89	multiple-choice	<p>What would be the advantage of surgery for the patient described in this scenario?</p>	f	100	0	2017-07-10 02:56:16	2017-07-10 02:56:16	3	22ac6023bb86b9e29e710414acdd8d68
107	90	essay	<p>Which intervertebral disk most likely is involved ?&nbsp;(25)</p>	f	100	0	2017-07-10 03:24:55	2017-07-12 02:57:14	3	50c85756ad8a1cb95cc4a7aa2fc01772
108	90	essay	<p>What is your diagnosis ?&nbsp;(25)</p>	f	100	0	2017-07-10 03:24:55	2017-07-12 02:57:14	3	0b57667d64792f626009f50d2bb9116b
109	90	essay	<p>What is your choice of treatment ?&nbsp;(25)</p>	f	100	0	2017-07-10 03:24:55	2017-07-12 02:57:14	3	cf8258ef264362c38614f47edf9eb89c
110	90	essay	<p>Before deciding upon surgical intervention, &nbsp;what &nbsp;is other important imaging modality to obtain ?&nbsp;(25)</p>	f	100	0	2017-07-10 03:24:55	2017-07-12 02:57:14	3	8ac48a4a3ae408b51cf6e3480d7ff428
111	91	essay	<p>Please describe at least 2 abnormality &nbsp;finding in MRI ! (40)</p>	f	100	0	2017-07-10 03:28:05	2017-07-12 02:56:40	3	aeffd5b0cc7e1abee1845e890b6ef352
112	91	essay	<p>Why the kyphotic deformity happened ? (30)</p>	f	100	0	2017-07-10 03:28:05	2017-07-12 02:56:41	3	59edb6151f854c41d31eb0320a7ada75
113	91	essay	<p>What is your diagnosis ? (30)</p>	f	100	0	2017-07-10 03:28:05	2017-07-12 02:56:41	3	9d312f6369eebe0bb2ac07c964d69e0c
114	92	essay	<p>Please explain why the leg pain improves with bending forward ?&nbsp;(25)</p>	f	100	0	2017-07-10 03:32:28	2017-07-12 02:56:11	3	e03a87c38c44027f84633fd260cdf204
115	92	essay	<p>What is your diagnosis ?&nbsp;(25)</p>	f	100	0	2017-07-10 03:32:28	2017-07-12 02:56:11	3	e0e90b3e813229622a0abed28fb7d874
116	92	essay	<p>What is your choice of treatment ?&nbsp;(25)</p>	f	100	0	2017-07-10 03:32:28	2017-07-12 02:56:11	3	4da14ce927c976891eb359d08ca7fc94
117	92	essay	<p>What is the most possible late complication ?&nbsp;(25)</p>	f	100	0	2017-07-10 03:32:28	2017-07-12 02:56:11	3	fd067de7c54a90ced462ea80224b3b8e
118	93	essay	<p>Please describe the abnormal radiologic finding at L1 ? (25)</p>	f	100	0	2017-07-10 03:35:15	2017-07-12 02:55:42	3	cdced876d74a833358ec8cfcb4281a76
119	93	essay	<p>What is your diagnosis &nbsp;?&nbsp;(25)</p>	f	100	0	2017-07-10 03:35:15	2017-07-12 02:55:42	3	9b3bb96b894525d13cba8043766e85a4
120	93	essay	<p>Explain biomechanically, why the kyphotic deformity tend to progress in severe osteoporotic spine ?&nbsp;(25)</p>	f	100	0	2017-07-10 03:35:15	2017-07-12 02:55:42	3	ad6e30dee11e04635719759b63662371
121	93	essay	<p>What is &nbsp;AAOS &nbsp;Clinical Practical &nbsp;Guidance recommendation &nbsp;about vertebroplasty ?&nbsp;(25)</p>	f	100	0	2017-07-10 03:35:15	2017-07-12 02:55:42	3	fde34140a3229422f791e4b1d36a9e6a
122	94	essay	<p>What is the mode of injury ? (25)</p>	f	100	0	2017-07-10 03:37:35	2017-07-12 02:55:12	3	566dbdc65899070809c101bd95752deb
123	94	essay	<p>What is your diagnosis ? (25)</p>	f	100	0	2017-07-10 03:37:35	2017-07-12 02:55:12	3	d1304a5797fba2f35f67b16d92bd753f
124	94	essay	<p>What is the most possible complication of this type of injury ? (25)</p>	f	100	0	2017-07-10 03:37:35	2017-07-12 02:55:12	3	3cb33444cf40a7476d7a2d70be7d5038
125	94	essay	<p>What is your best choice for treatment ? (25)</p>	f	100	0	2017-07-10 03:37:35	2017-07-12 02:55:12	3	d655e1b11dfebae1e341b40dcbfe5003
126	96	essay	<div>Describe the surgical margin in musculoskeletal tumor (30)</div>	f	100	0	2017-07-10 12:31:15	2017-07-10 12:31:15	3	29f20c3e0dc926de2b5401787de7892f
127	96	essay	<div>Which type of surgical margin related to the picture above and what is the possible diagnosis (35)</div>	f	100	0	2017-07-10 12:31:15	2017-07-11 21:34:13	3	41411942c71209d8c56a35cd260fbd9b
135	98	essay	<div>What is the step of Greenspan for reading the lesion in extremity(35)</div>	f	100	0	2017-07-10 12:49:20	2017-07-10 12:49:20	3	a618d11870959f0415d3c8944521e4a7
136	98	essay	<div>What is the possible diagnosis according to the x-ray and&nbsp; explain why(30)</div>	f	100	0	2017-07-10 12:49:20	2017-07-10 12:49:20	3	58d5d266ba038344b24e8e08ab06c2ea
137	98	essay	<div>What the meaning of periosteal reaction(35)</div>	f	100	0	2017-07-10 12:49:20	2017-07-10 12:49:20	3	9dbcea87d97dc50d974d7baf56d16449
138	99	essay	<p>What is your diagnosis and please classify the fracture&nbsp;(25)</p>	f	100	0	2017-07-10 14:06:16	2017-07-12 03:03:29	3	38e242454e2ee1fb51f87d0c8f43757a
139	99	essay	<p>How do you manage this problem&nbsp;(25)</p>	f	100	0	2017-07-10 14:06:16	2017-07-12 03:03:29	3	9597e83b532621f5dae6f72c497f30e6
140	99	essay	<p>If your answer on no 2 is surgical intervention, what approach do you use and why?&nbsp;(25)</p>	f	100	0	2017-07-10 14:06:16	2017-07-12 03:03:29	3	07f51b05c374e6a020f65cd90934289e
141	99	essay	<p>What complication may occur in the future&nbsp;(25)</p>	f	100	0	2017-07-10 14:06:16	2017-07-12 03:03:29	3	9b20a8cc8d921f850f205274d490dfb4
837	437	essay	<p>What is most likely diagnosis? (30)</p>	f	100	0	2018-12-02 10:24:39	2018-12-02 10:35:41	3	ba7e9eb3cc363302f9604437d22e4242
142	100	essay	<p>Please mention three&nbsp;differential diagnosis that may produce such deformities (40)</p>	f	100	0	2017-07-10 14:10:23	2017-07-12 14:37:16	3	256a5c15226cd2234094d5207f478a6a
143	100	essay	<p>An idiopathic clubfoot is best managed by Ponseti protocol. Could you describe in brief the protocol of Ponseti (60)</p>	f	100	0	2017-07-10 14:10:23	2017-07-12 03:03:05	3	f5ad2ca96b1e5cc6c445381738b287cf
144	101	essay	<p>What is the characteristics sign you found in this osteosarcoma histopathology? Please listed at least 3 sign and pointed to the figure A (50)</p>	f	100	0	2017-07-10 14:13:11	2017-07-11 21:48:50	3	d35dc6d45a952dc94231de8acb91fb2a
145	101	essay	<p>What is the characteristics sign you found in this Giant Cell Tumor of the bone histopathology? Please listed at least 2 sign and pointed to the figure B (50)</p>	f	100	0	2017-07-10 14:13:11	2017-07-10 14:13:31	3	99e1029bfa62069448a039ec569f0103
146	102	essay	<p>Please name your diagnosis and classification (30)</p>	f	100	0	2017-07-10 14:14:12	2017-07-12 03:02:36	3	b3e1321bb7c59fd930576adc22f287f3
147	102	essay	<p>Please describe the configuration and extension of fracture line in growth plate injury according to Salter Harris (40)</p>	f	100	0	2017-07-10 14:14:12	2017-07-12 03:02:36	3	d1ba42aaf1a98ddd1e296387f08b0065
148	102	essay	<p>How do you treat this condition? (30)</p>	f	100	0	2017-07-10 14:14:12	2017-07-12 03:02:36	3	3f49eaa86febcaba25c6fa70b3c1ac24
149	103	essay	<p>What is your diagnosis (40)</p>	f	100	0	2017-07-10 14:16:18	2017-07-12 03:02:05	3	76a2de4f4ecf4109b7919307480228d9
150	103	essay	<p>How do you manage this problem (60)</p>	f	100	0	2017-07-10 14:16:18	2017-07-12 03:02:05	3	ec01c0996944a826832a4af26859250c
153	105	essay	<p>What is your clinical diagnosis? (30)</p>	f	100	0	2017-07-10 14:23:21	2017-07-12 03:01:26	3	07252d6592af1c42a062b046234d0dd7
154	105	essay	<p>How do you manage this problem (30)</p>	f	100	0	2017-07-10 14:23:21	2017-07-12 03:01:26	3	b799a2f0f075b19633adba6e14aecd53
155	105	essay	<p>What complication may occur when the deformity is <strong>NOT</strong> treated (40)</p>	f	100	0	2017-07-10 14:23:21	2017-07-12 03:01:26	3	dc93fde83a7183a466b9ea04121a909d
156	106	essay	<p>Please describe the MRI finding !&nbsp;(25)</p>	f	100	0	2017-07-10 14:34:50	2017-07-12 02:58:17	3	b0b94b71338d22416e1f2d1e26e7225b
157	106	essay	<p>What is the diagnosis ?&nbsp;(25)</p>	f	100	0	2017-07-10 14:34:50	2017-07-12 02:58:17	3	03dd0d60f0d2c08ff488906d8475e807
158	106	essay	<p>How do you manage this patient ?&nbsp;(25)</p>	f	100	0	2017-07-10 14:34:50	2017-07-12 02:58:17	3	d822c3d078739fa5375f19ff46ffabdd
159	106	essay	<p>Please describe type of graft option for this reconstruction surgery?&nbsp;(25)</p>	f	100	0	2017-07-10 14:34:50	2017-07-12 02:58:17	3	aacb7a096b91ec526c5cd74e6b8aa00f
160	107	essay	<div>List 3 differential diagnosis <strong>(30)</strong></div>\n\n<div>&nbsp;</div>	f	100	0	2017-07-10 14:37:10	2017-07-10 14:37:10	3	ff29cb6450289a214da2021b1fed019b
161	107	essay	<p>What is the specific test (clinically) for each of your differential diagnosis? <strong>(35)</strong></p>	f	100	0	2017-07-10 14:37:10	2017-07-10 14:37:10	3	75e499d46b9d6ff5e049f36719de6346
162	107	essay	<p>List dorsal compartments of the wrist <strong>(35)</strong></p>	f	100	0	2017-07-10 14:37:10	2017-07-10 14:37:10	3	176d257114747766370fc4fe8733c2f0
163	108	essay	<p>What is your diagnosis? (25)</p>	f	100	0	2017-07-10 14:38:08	2017-07-10 14:38:08	3	177f9ba63ea5e56044af2bee8ebc3512
164	108	essay	<p>Explain the pathomechanism! (25)</p>	f	100	0	2017-07-10 14:38:08	2017-07-10 14:38:08	3	405c7211b6301d2c95c9031b81601220
165	108	essay	<p>Please describe the classification of this disease! (25)</p>	f	100	0	2017-07-10 14:38:08	2017-07-10 14:38:08	3	8b64ee763fe8b9915d5c79545324966a
166	108	essay	<p>Please mention the risk factors for this disease? (25)</p>	f	100	0	2017-07-10 14:38:08	2017-07-10 14:38:08	3	7389804b55b83a742189f15d5e1ed160
167	110	essay	<p>Describe the shoulder MRI results ! (25)</p>	f	100	0	2017-07-10 14:40:59	2017-07-10 14:40:59	3	2da5cb3690108012884278c62048a5a8
168	110	essay	<p>What is the pathomechanism of this pathological condition? (25)</p>	f	100	0	2017-07-10 14:40:59	2017-07-10 14:40:59	3	ddfdc57491b86b44d696ac9028de23cb
169	110	essay	<p>What is the most appropriate next treatment step? (25)</p>	f	100	0	2017-07-10 14:40:59	2017-07-10 14:40:59	3	1e4f5fcd14be2ff7e2b76fbd69f1c1d0
170	110	essay	<p>When the patient can return to sport activities? (25)</p>	f	100	0	2017-07-10 14:40:59	2017-07-10 14:40:59	3	291840f0ef671de9b94757509d31cc52
171	109	essay	<div>What structure prevents flexor tendon from retracting proximally when ruptured? (35)</div>\n\n<div>&nbsp;</div>	f	100	0	2017-07-10 14:41:41	2017-07-10 14:41:41	3	610434ff553d4cc77ae70f5a91dff48f
172	109	essay	<p>What is the source of flexor tendon nutrition? (35)</p>	f	100	0	2017-07-10 14:41:41	2017-07-10 14:41:41	3	8efa2d2fedfc43c3535f6cd0c105f4bf
173	109	essay	<p>3 months after tendon repair, he complaining that the adjacent finger cannot full flexed when grasping while the repaired finger can fully flex, what is the problem called? (30)</p>	f	100	0	2017-07-10 14:41:41	2017-07-10 14:41:41	3	1801cb058d4ae044143d2ae86ea3cf95
174	112	essay	<p>Please describe the clinical and X-ray findings? (25)</p>	f	100	0	2017-07-10 14:45:01	2017-07-10 14:45:01	3	71864da25059db8a6c0a3ddc692dbc8d
175	112	essay	<p>What are possible clinical diagnosis? (25)</p>	f	100	0	2017-07-10 14:45:01	2017-07-10 14:45:01	3	a24ac4baa6dfe0daa992238110a6f78b
176	112	essay	<p>What are the complications ? (25)</p>	f	100	0	2017-07-10 14:45:01	2017-07-10 14:45:01	3	676b0fd2938433c882bd37e84cad96d6
177	112	essay	<p>Please describe the reduction technique? (25)</p>	f	100	0	2017-07-10 14:45:01	2017-07-10 14:45:01	3	62831782660af8ad7306e6a96d3ec36f
178	111	essay	<div>What is the the most possible diagnosis of the previous injury (20)</div>\n\n<div>&nbsp;</div>	f	100	0	2017-07-10 14:45:50	2017-07-10 14:45:50	3	7ac0ab179ded41b239846808f4d9093f
179	111	essay	<p>What is the pathoanatomic of point 1? (20)</p>	f	100	0	2017-07-10 14:45:50	2017-07-10 14:45:50	3	b3fea9a56a632a7eb5f6fec9b0500cbe
180	111	essay	<p>What is the deformity now? (20)</p>	f	100	0	2017-07-10 14:45:50	2017-07-10 14:45:50	3	9ebcf7c1d6459152be28709e7fe86f62
181	111	essay	<p>List 3 pathoanatomic of point 3 in this case ? (40)</p>	f	100	0	2017-07-10 14:45:50	2017-07-10 14:45:50	3	f56a7049e72bef519ce18df3f3789363
182	113	essay	<p>What is the diagnosis? (25)</p>	f	100	0	2017-07-10 14:48:29	2017-07-10 14:48:29	3	96a42e39b8838d45255071947c4929c6
183	113	essay	<p>What is the basic pathology of this condition? (25)</p>	f	100	0	2017-07-10 14:48:29	2017-07-10 14:48:29	3	6f1aaa1ce19a0bea7697bc5d36c16565
184	113	essay	<p>Please mention special test to establish the diagnosis? (25)</p>	f	100	0	2017-07-10 14:48:29	2017-07-10 14:48:29	3	d8162d852b63c861d8055f7e5664efea
185	113	essay	<p>What is the principle of operative treatment for this condition? (25)</p>	f	100	0	2017-07-10 14:48:29	2017-07-10 14:48:29	3	999a378fe9da17c601aa81899170d730
186	114	essay	<div>Define finger tip injury classification according to Allen (30)</div>\n\n<div>&nbsp;</div>	f	100	0	2017-07-10 14:48:38	2017-07-10 14:48:38	3	6229f4314d336dd9c48cc236ada69199
187	114	essay	<p>What complication is shown in clinical picture? (20)</p>	f	100	0	2017-07-10 14:48:38	2017-07-10 14:48:38	3	3591894e393aa40fc2c72b83b338f0e4
188	114	essay	<p>Why do no. 2 is occurring? (20)</p>	f	100	0	2017-07-10 14:48:38	2017-07-10 14:48:38	3	02d4ed4c0f8fd3533cfdebd39d4e872e
189	114	essay	<p>List 2 common patient complaints after finger tip injury, regardless of the treatment chosen (30)</p>	f	100	0	2017-07-10 14:48:38	2017-07-10 14:48:38	3	080d9e34c600d58f38959ff302b542a4
190	115	essay	<p>How to do closed reduction? (25)</p>	f	100	0	2017-07-10 14:50:48	2017-07-10 14:50:48	3	c341f07c647f1367f7f5661c90b7251a
192	115	essay	<p>List 2 operative management option? (25)</p>	f	100	0	2017-07-10 14:50:48	2017-07-10 14:50:48	3	fae7b5b52fb757aa622cefa895c9f698
193	115	essay	<p>What is the acceptable angulation for 2nd-5th metacarpal neck fracture? (25)</p>	f	100	0	2017-07-10 14:50:48	2017-07-10 14:50:48	3	b3220a30a9c9fd62313be26629128dd6
194	116	essay	<div>Describe about Mirels classification (35)</div>	f	100	0	2017-07-11 21:54:12	2017-07-11 21:54:12	3	f4fccbb7a6fa109e2e422ad862529dac
195	116	essay	<div>Describe about at least 3 tumor marker (35)</div>	f	100	0	2017-07-11 21:54:12	2017-07-11 21:54:12	3	49a98540323d29e72875c05c1c0260f7
196	116	essay	<div>Describe about &ldquo;seed and soil&rdquo; theory (30)</div>	f	100	0	2017-07-11 21:54:12	2017-07-11 21:54:12	3	d12f10cbe00a5e6dd4ad65fe5bba8c22
197	119	multiple-choice	<p>Figures above are the MR images of a 74-year-old man who has progressive gait and balance difficulties with moderate neck pain. Examination reveals intrinsic weakness (4/5), hyperreflexia in the triceps and quadriceps, and a positive Hoffman sign. What is the most appropriate treatment?</p>	t	100	0	2017-12-07 18:25:24	2018-01-04 04:16:54	3	e2562e862887b008752fd6f735a6351b
198	120	multiple-choice	<p>The figure below is the MR image of a 68-year-old woman who has had neck pain for several years. She has noticed the progression of gait imbalance and changes in her handwriting. What is the best next step?</p>	f	100	0	2017-12-07 18:40:58	2017-12-10 09:29:41	3	b1ebbce45d920665aad69653f278cd4f
199	121	multiple-choice	<p>What is the diagnosis?</p>	t	100	0	2017-12-07 18:56:13	2017-12-07 18:56:13	3	3e1c9d7a6ddbd995f3e74df2774c79d0
200	121	multiple-choice	<p>He has attempted nonsurgical care including physical therapy and medications for 6 months. But the pain is still intolerable, and restrict his daily activity.&nbsp; What is the most appropriate treatment?</p>	t	100	0	2017-12-07 18:56:13	2017-12-07 18:56:13	3	07dcdf00b7925db11337990ea944a3ba
201	122	multiple-choice	<p>What is the most accurate American Spinal Injury Association (ASIA) classification for this injury?</p>	t	100	0	2017-12-07 19:03:16	2017-12-07 19:03:16	3	8f4647e575d6159a7b68a3e26071a814
202	122	multiple-choice	<p>The above patient has a heart rate of 50 and a blood pressure of 60/40 mm Hg. No other thoracic or abdominal injuries are found. The patient is resuscitated according to Advanced Trauma Life Support guidelines, but there is no significant change in his vital signs. What is the cause of the shock?</p>	t	100	0	2017-12-07 19:03:16	2017-12-07 19:03:16	3	ec34d157e0887b3ec3547c567980acd9
204	124	multiple-choice	<p>This is&nbsp; MR images of a 70-year-old man who has intolerable radiating pain into both legs, with mild back pain. His legs pain improves with bending forward or sitting position and worsens with standing and walking. Her neurologic examination reveals normal motor power.&nbsp; He had nonsurgical care including physical therapy and medications for 3 months. What is the most appropriate treatment?</p>	f	100	0	2017-12-07 19:14:20	2017-12-10 09:37:05	3	b32b08ca8e888b7b0d7dfb5f0937f6d8
205	125	multiple-choice	<p>A&nbsp; 70 -year-old man with a long-standing history of neck pain. He slipped and fell in his bathtub, which resulted from the weakness of upper extremity ( 1/5) and lower extremities (4/5).&nbsp; What is the most likely mode of injury in this scenario?</p>	t	100	0	2017-12-07 19:19:59	2017-12-07 19:19:59	3	9d8a8d465202fc1b4cb1f52a4b97f1aa
206	126	multiple-choice	<p>Examination findings are most likely to indicate decreased sensation in the left :</p>	f	100	0	2017-12-07 19:29:57	2018-01-03 13:30:22	3	c41763965fb11188f005fedb03882899
207	126	multiple-choice	<p>What is the most appropriate treatment for this&nbsp;mentioned case?&nbsp;<strong> &nbsp;</strong></p>	f	100	0	2017-12-07 19:29:57	2018-01-03 13:30:22	3	38d56bc9e67bcb02046b19681e49e444
208	127	multiple-choice	<p>What is the most likely mode of injury in this scenario?</p>	f	100	0	2017-12-07 19:48:16	2017-12-10 09:45:25	3	fba9dc0de19d2d0692d1190b6fc4236b
209	127	multiple-choice	<p>What is the most appropriate early treatment for the this mentioned case?</p>	f	100	0	2017-12-07 19:48:16	2017-12-10 09:45:25	3	97fbbe7d028a7c9a7993cd8c00d89206
210	128	multiple-choice	<p>According to the AAOS Clinical Practice Guideline, Treatment of Symptomatic Osteoporotic Spinal Compression&nbsp; Fractures, a strong recommendation is made</p>	f	100	0	2017-12-07 19:53:28	2018-01-03 10:51:17	3	f0824abe4687a1f7fb06e5810fd9998d
211	129	multiple-choice	<p>Which root most likely is involved?</p>	t	100	0	2017-12-07 20:42:29	2017-12-07 20:42:29	3	f41f8a6a06fd3d3bac702b3c7afbe9b6
212	129	multiple-choice	<p>Based on the aforementioned physical finding, Which intervertebral disk most likely is involved?</p>	t	100	0	2017-12-07 20:42:29	2017-12-07 20:42:29	3	26d7c267b4c167a761544734675e5653
213	130	multiple-choice	<p>What is the most possible underlying disease?</p>	f	100	0	2017-12-07 20:57:20	2017-12-26 08:34:12	3	c4ed43c484fcfd15473cbd4c5802b51e
214	130	multiple-choice	<p>What is the most appropriate treatment for the aforementioned case?</p>	f	100	0	2017-12-07 20:57:20	2017-12-26 08:34:12	3	276314b2d1883f0fe421a8d8261d7e2c
215	131	multiple-choice	<p>Angle &ldquo;x&rdquo; refers to which radiographic parameter?</p>	f	100	0	2017-12-07 21:09:08	2017-12-10 09:52:20	3	82f078aaf881098da4c34d663fd1f6d0
216	131	multiple-choice	<p>These statements are true&nbsp; regarding &nbsp;sacral parameters, <strong>EXCEPT </strong>:</p>	f	100	0	2017-12-07 21:09:08	2017-12-10 09:52:20	3	fc21af6b24978eee448ab5867a34277f
217	132	multiple-choice	<p>Figures above are the gadolinium-enhanced MRI scans of a 68-year-old woman with intermittent midthigh pain for 2 months. She has an anterolateral proximal thigh mass of approximately 9 cm. Radiographs reveal no bone lesion, but there is a soft-tissue mass. A needle biopsy of the thigh lesion is seen in the figure above. What is the most likely diagnosis?</p>	f	100	0	2017-12-07 21:17:48	2017-12-10 09:55:05	3	b567b851234189c41d638e7465e7781f
236	151	multiple-choice	<p>A 20-year-old man used his fist to hit another man in the mouth. Examination3 hours after the injury shows a 1-cm laceration over the third metacarpophalangeal joint. Treatment should consist of which of the following?</p>	f	100	0	2017-12-08 04:43:38	2017-12-10 10:35:57	3	a370903a8490e6ac05e190bf1c91a92d
348	200	essay	<p>Please describe the histopathology&nbsp;finding in figure 4b! (30)</p>	f	100	0	2017-12-10 04:05:16	2018-01-03 23:27:37	3	ebd06a077ac08f0c553246984656f9b4
218	133	multiple-choice	<p>A 25-year-old man was struck by a car and sustained an open tibial shaft fracture treated with medullary nailing. Preoperatively, he had a heart rate of 92 beats/min and a BP of 144/72 mm Hg. He was awake and alert and had isolated right leg pain. His pain was not exacerbated by a passive range of motion of the right ankle or toes, and his leg compartments were soft. At the end of procedure, his leg<strong> </strong>felt tense. Anterior compartment<strong> </strong>pressure was noted 25 mmHg. The patient&rsquo;s heart rate was 52 beats/min and his blood pressure was 100/50 mm Hg. The appropriate treatment<strong> is to</strong></p>	f	100	0	2017-12-07 21:33:43	2017-12-10 10:00:12	3	5631e9c0d7e4a1e7abb7d2fcb56b0e62
219	134	multiple-choice	<p>The figure above are the lateral plain radiograph and postcontrast sagittal and axial T1-weighted MRI scans of a 20-year-old man with worsening back pain for 3 weeks. He notes malaise, nausea, and emesis but denies fevers. Findings from his neurologic examination are normal. Laboratory studies show a white blood cell count of 9,200/mm3, an erythrocyte sedimentation rate of 65 mm/h (reference range, 0-20 mm/h), and C-reactive protein of 9.5 mg/L (reference range, 0-3.0 mg/L). A guided biopsy was performed and revealed methicillin-sensitive <em>Staphylococcus aureus</em>. In addition to parenteral antibiotics, what is the most appropriate treatment?</p>	t	100	0	2017-12-07 21:38:56	2017-12-07 21:38:56	3	873dd8e028b76bc8c3099d540af320e2
222	137	multiple-choice	<p>One week after closed reduction of a distal radius fracture, an 11-year-old re-develops the initial deformity of 25 degrees apex volar angulation and 8 mm of dorsal displacement. What is the most appropriate treatment<strong>? </strong></p>	t	100	0	2017-12-07 21:49:11	2017-12-07 21:49:11	3	19a137c88d05f6892dc1e53d21107367
223	138	multiple-choice	<p>Premature arrest following growth plate injury is attributed to what mechanism?</p>	t	100	0	2017-12-07 21:51:41	2017-12-07 21:51:41	3	73a2668097adfcd90f2939e765d076f5
224	139	multiple-choice	<p>A 1-week-old infant was placed in a Pavlik harness for an Ortolani-positive hip. She was seen on a weekly basis and her hip remained dislocated 3 weeks later. What is the most appropriate treatment?</p>	f	100	0	2017-12-07 21:54:11	2017-12-10 10:13:05	3	e79dde177a87ab1b6e1ab97b893157d7
225	140	multiple-choice	<p>What is the most common anatomic location of the lateral femoral cutaneous nerve?</p>	t	100	0	2017-12-07 21:56:33	2018-01-03 09:39:24	3	235d00cc1c2e89398a8b6a49615aaa71
226	141	multiple-choice	<p>Long-term alendronate use for osteoporosis has been associated with which of the following?</p>	f	100	0	2017-12-07 22:01:23	2017-12-10 10:15:29	3	c127d39849f8d42340ed6cbff48ce7b7
227	142	multiple-choice	<p>Which of the following factors is most likely to contribute to pseudarthrosis in a patient who has undergone a single-level anterior decompression and fusion procedure for the treatment of cervical radiculopathy?</p>	t	100	0	2017-12-07 22:04:17	2018-01-04 04:15:44	3	b6da544a1ac155105fe77bc0ec407be2
228	143	multiple-choice	<p>A 25-year-old man is brought to the emergency department following a motor vehicle accident. Extrication time was 2 hours, and in the field, he had a systolic blood pressure by palpation of 90 mmHg. Intravenous fluid therapy was started, and on arrival to the emergency department, his systolic blood pressure is 90 mm Hg with a pulse rate of 130. Examination reveals a flail chest and a femoral diaphyseal fracture. Ultrasound of the abdomen shows increasing fluid in Morrison Pouch. The trauma surgeons take him to the operating room for an exploratory laparotomy. At the end&nbsp;of the procedure, systolic pressure is 100 mm Hg with a pulse rate of 110. Oxygen saturation is 90% on 100% oxygen, and the patient&rsquo;s temperature is 95 degrees&nbsp;of F (35 degrees C). What is the recommended treatment of the femoral fracture at this time?</p>	f	100	0	2017-12-07 22:13:49	2017-12-10 09:24:57	3	7f01ef81606b64bff691b74137b701a2
229	144	multiple-choice	<p>A 14-year-old boy undergoes application of a circular frame with tibial and fibular osteotomy for gradual limb lengthening. He initiates lengthening 7 days after surgery. During the first week of lengthening, he reports that turning of the distraction device is becoming increasingly difficult. On the 9th day of lengthening, he is seen in the emergency department after feeling a pop in his leg and noting the acute onset of severe pain. What complication has most likely occurred?</p>	f	100	0	2017-12-07 22:17:56	2017-12-10 10:21:31	3	44aa038efcb872dc7a1b4cb3115c00ff
230	145	multiple-choice	<p>A 49-year-old woman with serologically proven rheumatoid arthritis has Larsen grade II (one or several small erosions) radiographic changes in the elbow. Examination reveals a preoperative arc of flexion of less than 90 degrees and there is no instability. Nonsurgical management has failed to provide relief. What is the best treatment options?</p>	f	100	0	2017-12-07 22:21:29	2017-12-10 10:24:37	3	4cc4b3a393f17cee27f20000c200582e
231	146	multiple-choice	<p>A 30-year-old man who sustained a tibial fracture with a peroneal nerve palsy 2 years ago now has foot drop and weak eversion of the foot. He reports success with stretching exercises, but he catches his toes when his foot tires. Examination reveals that the foot is plantigrade and supple. What is the most appropriate next step in management?</p>	t	100	0	2017-12-07 22:25:39	2017-12-10 10:25:43	3	7bf5c08b01a8055ef8788b60b4134e9f
232	147	multiple-choice	<p>A 25-year-old man has a midshaft femoral fracture with 25% comminution and is undergoing closed intramedullary nailing. Proximal locking is performed uneventfully; however, during distal locking screw insertion, only one of the screws is noted to have bone purchase. Which of the following procedures is the best solution to this problem?</p>	f	100	0	2017-12-07 22:29:43	2017-12-10 10:26:59	3	3cf9b038d2b68419e76f38770b1b46b0
233	148	multiple-choice	<p>A 23-year-old man has an isolated open tibial fracture without distal neurologic or vascular compromise following a motorcycle accident. After undergoing skeletal stabilization and several debridements, a clean 6x6-cm wound remains over the anteromedial surface of the distal third of the tibia. The tibia is exposed throughout the length of the wound and the periosteum has been stripped. What is the best option for wound management at this time?</p>	f	100	0	2017-12-07 22:33:05	2017-12-08 04:40:25	3	5631541bd1f54f972eaa36c143e6cd04
234	149	multiple-choice	<p>A 12-year-old boy has a solitary osteochondroma arising from the medial cortex of the distal femur. The lesion is not painful, nor is it causing any disability. What surgical stage would be assigned according to the system of the Musculoskeletal Tumor Society:</p>	f	100	0	2017-12-08 04:36:11	2017-12-08 04:39:56	3	dba6bc6bf5cbedb39438bafd99ea30f9
235	150	multiple-choice	<p>A 2-year-old boy will not bear weight after tripping over a curb. He is afebrile. Laboratory studies show a WBC count of 6,000/mm3 (normal 3,500 to 10,500/mm3) and an erythrocyte sedimentation rate of 10 mm/h (normal up to 20 mm/h). Examination reveals reproducible tenderness over the midshaft of the right tibia. AP and lateral radiographs of the right femur and tibia shows no obvious fracture line. What is the next most appropriate step in management?</p>	f	100	0	2017-12-08 04:39:24	2018-01-03 16:12:27	3	0883f049b38f767a08d2bebb7df08b12
346	200	essay	<p>Please describe the histopathology&nbsp;finding in figure 4a! (30)</p>	f	100	0	2017-12-10 04:05:16	2018-01-03 23:27:37	3	e6f8c4cfabc522e4a310a527ad98fdf4
237	152	multiple-choice	<p>An 8-year-old boy fell off a playground structure and sustained a closed Galeazzi fracture with apex dorsal angulation of radius. What is the best initial treatment?</p>	t	100	0	2017-12-08 04:45:57	2017-12-08 04:45:57	3	42e5160b06ca52a4c1d52b5410bdd68e
239	154	multiple-choice	<p>A 25-year-old female is diagnosed with closed fracture (simple transverse) of the left femur middle third. The fractured femur was fixed using unreamed locked intramedullary nailing system (distal screw was not inserted). Which of the following healing process will mostly occurred in this patient?</p>	f	100	0	2017-12-08 04:55:32	2017-12-10 10:41:40	3	f5c960570c51a8cc6caf55d39165627b
240	155	multiple-choice	<p>Plate and screw fixation can achieve ideal stabilization for long bone fracture. The rigidity of the construct is directly correlated to :</p>	f	100	0	2017-12-08 04:57:56	2017-12-10 10:44:51	3	5fe3c432a9163783803e27fb31f7a0d8
241	156	multiple-choice	<p>As an orthopedic implant, titanium alloy have a wider area of elastic zone in comparison of stainless steel 316L. This description will be most suitable for?</p>	f	100	0	2017-12-08 05:22:45	2017-12-10 10:45:50	3	9e91e26f3376d32c45688b67c74eb01b
242	157	multiple-choice	<p>Bone is a unique material structure that will exhibit different mechanical properties depending on the direction of the applied force, this description is best describe as?</p>	t	100	0	2017-12-08 05:26:46	2017-12-08 05:26:46	3	95fec3ede8800265ae45937abd6efa96
243	158	multiple-choice	<p>A 55-year-old man came to the clinic with a chief of complain clumsiness on both of his hands. He also experiences walking difficulty, however still be able to walk around his house independently using a tripod cane. Based on the above description which of the following diagnosis more suitable for this patient?</p>	f	100	0	2017-12-08 05:31:15	2017-12-26 08:45:52	3	9e54a63a5d7d93a90dcf5245cebc314f
244	159	multiple-choice	<p>Based on the diagnosis which of the following statement is more suitable?</p>	f	100	0	2017-12-08 05:40:34	2017-12-10 10:52:37	3	fbcfe25516e369296dc0a686bb759cc3
996	522	essay	<p>What would be your initial fracture management for this kind of fracture? (25)</p>	f	100	0	2018-12-17 02:55:25	2018-12-17 02:55:25	3	b861ade7a550a79551ae5fb0aed041d1
245	159	multiple-choice	<p>To attain proper&nbsp;culture and best residing bacteria that causes the infection it is best to take&nbsp;the specimen&nbsp;from?</p>	f	100	0	2017-12-08 05:40:34	2017-12-10 10:52:37	3	edb027269f0e7650bf9377aee16c74c5
246	159	multiple-choice	<p>During the debridement, the orthopedic surgeon encounters large dead space resulted from the active tissue and bone infection. Which of the following is most suitable to choose to fill in the dead space?</p>	f	100	0	2017-12-08 05:40:34	2017-12-10 10:52:37	3	9f93e1211b19b6486ab01d883b8e092a
247	160	multiple-choice	<p>What do you do next?</p>	f	100	0	2017-12-08 22:28:39	2017-12-10 11:05:59	3	c1953e71a35f756d69124169c9829b5c
248	160	multiple-choice	<p>After performing the procedure, you obtain the AP pelvic seen in Picture 2. What is the next appropriate step?</p>	f	100	0	2017-12-08 22:28:39	2017-12-10 11:05:59	3	f0ff5483ccda55ba3ba1cbfbefe8db17
249	160	multiple-choice	<p>What structure is most at risk with distal femoral traction pin placement?</p>	f	100	0	2017-12-08 22:28:39	2017-12-10 11:05:59	3	104e443c4c910cb35a6f3be87a99c57a
250	160	multiple-choice	<p>Nine months after open reduction and internal fixation of his posterior acetabulum fracture, the patient presents to clinic with continued right hip pain deep in his groin that is worse with internal rotation and walking up stairs. Repeat radiographs of his hip reveal a well-healed acetabular fracture and normal appearance of femoral head. What is the most likely etiology of his hip pain?</p>	f	100	0	2017-12-08 22:28:39	2017-12-10 11:05:59	3	2775472a07f168f4806c4fd09c9a53e0
251	161	multiple-choice	<p>All of the following are contained within the deep posterior compartment of the leg EXCEPT:</p>	t	100	0	2017-12-08 22:40:53	2017-12-08 22:41:47	3	96be500a6c869fc7c34745d8e89abfbd
252	161	multiple-choice	<p>The patient is indicated for intramedullary nailing of this closed fracture. The patient is positioned supine with the knee in a flexed position, and a medial parapatellar approach is used. Which of the following statements regarding the operative management of this injury is false?</p>	t	100	0	2017-12-08 22:40:54	2017-12-08 22:41:47	3	217f4a8db56ff7b9bb3cb2868a62d412
253	161	multiple-choice	<p>The patient undergoes successful intramedullary nailing and is transferred to the medical surgical floor. Six hours postoperatively, the patient begins to complain of progressively worsening anterior leg pain that is exacerbated by ankle motion. Palpable pulses are present distally. The patient&rsquo;s blood pressure is measured to be 107/66 mm Hg. Which statement accurately describes the appropriate evaluation of the patient&rsquo;s condition?</p>	t	100	0	2017-12-08 22:40:54	2017-12-08 22:41:47	3	2f48009fa79fffd79a3e695546c3fc53
254	161	multiple-choice	<p>When assessing the adequacy of reduction of a tibia fracture, all of the following are considered acceptable parameters EXCEPT:</p>	t	100	0	2017-12-08 22:40:54	2017-12-08 22:41:47	3	8bf41b8dbe5d7e996353cd2476ea4831
256	162	multiple-choice	<p>Regarding the anatomy of the ankle joint, all of the following are correct <strong>EXCEPT</strong>:</p>	f	100	0	2017-12-09 00:19:54	2017-12-10 11:17:09	3	f336bca6a02324598c5517d8009c50d9
257	162	multiple-choice	<p>Which of the following statements accurately describes the pattern of injury in one of the four types of ankle fractures as described in the Lauge&ndash;Hansen classification?</p>	f	100	0	2017-12-09 00:19:54	2017-12-10 11:17:09	3	6dcd82cfc9f5afbb371efc52b2e019e1
258	162	multiple-choice	<p>Which of the following statements regarding the Danis&ndash;Weber classification system is <strong>CORRECT</strong>?</p>	f	100	0	2017-12-09 00:19:54	2017-12-10 11:17:09	3	27e90eb03fe9237d54f3aa8d774828e6
259	162	multiple-choice	<p>A supination&ndash;adduction type injury should raise one&rsquo;s concern for which of the following associated findings that may significantly impact the patient&rsquo;s long&shy;term functional outcome?</p>	f	100	0	2017-12-09 00:19:54	2017-12-10 11:17:09	3	2d277ef428977c7e6766860d6da33641
260	163	multiple-choice	<p>Which of the following is not included in Mirel scoring system?</p>	t	100	0	2017-12-09 00:21:33	2017-12-10 11:21:20	3	613d9249503c2e2cfcf21b8a173e895b
261	163	multiple-choice	<p>What is the mechanism behind bone destruction in a metastatic lesion?</p>	t	100	0	2017-12-09 00:21:33	2017-12-10 11:21:20	3	eb6c2858ccb2d68b23e6f1d85c19744d
262	163	multiple-choice	<p>What intervention would you recommend for her hip if you found mirel score was 10 ?</p>	t	100	0	2017-12-09 00:21:33	2017-12-10 11:21:20	3	a526647284d96074f64034227ef71c92
263	164	multiple-choice	<p>What is the most likely diagnosis?</p>	f	100	0	2017-12-09 00:34:02	2018-01-03 09:57:25	3	a5f7db27a7f2a01beb7fb8c5d0cbf4bd
264	164	multiple-choice	<p>All of the following are true statements if fracture happens in this patient, EXCEPT:</p>	f	100	0	2017-12-09 00:34:02	2018-01-03 09:57:25	3	b83963ce799db4ebd56079e4138bb7c7
265	164	multiple-choice	<p>What is the recommended management of this lesion?</p>	f	100	0	2017-12-09 00:34:02	2018-01-03 09:57:25	3	071714db8a8e8d4dc53f027d5096146b
347	200	essay	<p>What is the diagnosis of figure 4a? (20)</p>	f	100	0	2017-12-10 04:05:16	2018-01-03 23:27:37	3	b8bf20fc5b220f5a7ff7c8eb869194c3
266	166	multiple-choice	<p>Japanese Orthopedic Association (JOA) score can quantify and objectively scored patient with cervical spondylotic myelopathy. The following condition is NOT considered during the evaluation of JOA score.</p>	f	100	0	2017-12-09 00:44:13	2018-01-03 13:34:28	3	696e43acbb560e1575612622d7b30c13
267	167	multiple-choice	<p>A 45-year-old woman came to you with the radiologic description of &lsquo;inverted Napoleon Hat sign&rsquo; on her AP view Lumbosacral X-Ray. What is most likely to be her diagnosis?</p>	t	100	0	2017-12-09 00:47:16	2017-12-09 00:47:16	3	63a6ef654b66066f575801597a4116f1
268	165	multiple-choice	<p>Which of the following is a more consistent physical examination finding in a Charcot foot than in an infected diabetic foot?</p>	f	100	0	2017-12-09 00:47:43	2018-01-03 13:28:08	3	1af93d8e4a12787b75064f8819fb5340
269	165	multiple-choice	<p>A lateral x-ray of the patient&rsquo;s foot is obtained. The patient asks if this location for neuropathic joint changes in the foot is common. You respond:</p>	f	100	0	2017-12-09 00:47:43	2018-01-03 13:28:08	3	9b4ef103129079d82bf124a73f24d8ee
270	165	multiple-choice	<p>The patient wants to know if he needs surgery at this point in time. You explain that there is no clear indication for surgery at this point given that there is no ulcer formation, or skin at risk. You recommend initially treating him with a total contact cast, and nonweight bearing for this stage of his disease. The reason for this is:</p>	f	100	0	2017-12-09 00:47:43	2018-01-03 13:28:08	3	c012aae359e5f7d9bd12933a0d884465
271	165	multiple-choice	<p>After 8 months of follow-up the patient is found to have no further progression of his deformity and no erythema or warmth present. He still is having difficulty in shower, even with accommodative orthotics. He now presents with an ulcer on the plantar aspect of his foot. Radiographs demonstrate no progression of deformity. The best treatment option at this point is:</p>	f	100	0	2017-12-09 00:47:43	2018-01-03 13:28:08	3	9892dea83141d227cdb513eb77b96081
997	522	essay	<p>What complications are you going to warn the patient about? (25)</p>	f	100	0	2018-12-17 02:55:25	2018-12-17 02:55:25	3	dd61fb423a6ff33a03562a464a414950
272	168	multiple-choice	<p>A sagittal cut MRI of a 34-year-old male showed high-intensity zone (HIZ) on the T2 weighted image of the L4 &ndash; L5 posterior annulus of the intervertebral disc. The axial cut of the same level also showed left foraminal disc protrusion. This condition will likely correlate with?</p>	t	100	0	2017-12-09 00:49:40	2017-12-09 00:49:40	3	0a24d297a2cb38376cb15f32e0c75b36
273	170	multiple-choice	<p>Modular implant in hip arthroplasty has given better versatility for the orthopedic surgeon during the implantation and surgical process. Despite the advantage, micromotion on the femoral head and neck junction can lead to future implant failure that is known as?</p>	t	100	0	2017-12-09 00:54:43	2017-12-09 00:54:43	3	72cb23013a27fde1479f6d83b4f951fc
274	171	multiple-choice	<p>Based on the previous description what is the most possible x-ray finding for this patient?</p>	f	100	0	2017-12-09 01:03:07	2017-12-09 01:04:37	3	195d53bcc621227da2296e1f94222100
275	171	multiple-choice	<p>The orthopedic surgeon also found positive (+) Rheumatoid Factor (RF) in this patient. The following description is TRUE related to RF.</p>	f	100	0	2017-12-09 01:03:07	2017-12-09 01:04:37	3	e98e9e5cbc85fffdf78b3b1dcc52cde3
276	172	multiple-choice	<p>Based on the previous description what is the most possible x-ray finding for this patient?</p>	f	100	0	2017-12-09 05:57:21	2017-12-10 11:33:53	3	9c8c700378ab7ceceb5167341db43f37
277	172	multiple-choice	<p>The orthopedic surgeon also found positive (+) Rheumatoid Factor (RF) in this patient. The following description is <strong>TRUE</strong> related to RF.</p>	t	100	0	2017-12-09 05:57:21	2017-12-10 11:33:53	3	38be89d992779064c3973a7b6730e634
278	173	multiple-choice	<p>After a thorough examination a girl is diagnosed with Adolescence Idiopathic Scoliosis Lenke 3- B N. This diagnosis will correspond to which of the following findings?</p>	f	100	0	2017-12-09 06:00:20	2017-12-10 11:35:17	3	31847fafcb17f083062647b692f05133
279	174	multiple-choice	<p>Based on the above description what is this girl&rsquo;s diagnosis?</p>	f	100	0	2017-12-09 06:09:02	2018-01-04 11:16:50	3	88d4536b4c5189444bd5b18390dd012c
280	174	multiple-choice	<p>Pelvic x ray of this patient showed 20% of ossification in the iliac apophysis, this finding will be correlated with?</p>	f	100	0	2017-12-09 06:09:02	2018-01-04 11:16:50	3	727806f391d8a0885fcc47e973ba1d1a
281	174	multiple-choice	<p>Best management for this patient is?</p>	f	100	0	2017-12-09 06:09:02	2018-01-04 11:16:50	3	77df4b1d34d85bf0bdc20cc203bc5232
282	175	multiple-choice	<p>The x-ray on both of the knee showed bilateral varus deformity with thickening and widening of the growth plate and flared metaphysis. What is the most possible diagnosis for this girl?</p>	t	100	0	2017-12-09 06:16:52	2017-12-09 06:16:52	3	16e443657729cbd75a99527a931ff407
283	175	multiple-choice	<p>Based on the above scenario and possible diagnosis what is the most possible pathophysiology that can best explain the patient&rsquo;s conditions?</p>	t	100	0	2017-12-09 06:16:52	2017-12-09 06:16:52	3	9d7de281f96d9f94425c97ff5ce2660e
284	175	multiple-choice	<p>Hypophosphatemic condition and the inability of controlling calcium level in this patient may resulted to secondary hyperparathyroidism that will correlate to this following clinical condition</p>	t	100	0	2017-12-09 06:16:52	2017-12-09 06:16:52	3	60e6ba88c8ed3179d06f5b7c3d6bf989
285	176	multiple-choice	<p>Which of the following terms best classify this patient&#39;s cerebral palsy?</p>	f	100	0	2017-12-09 06:24:01	2017-12-10 11:44:07	3	3476f17b8e27814bd9f36cd8ebf2bd11
286	176	multiple-choice	<p>Despite the main diagnosis of the patient, what is most likely happen in the hip of this patient?</p>	t	100	0	2017-12-09 06:24:01	2017-12-10 11:44:07	3	fa39803eb5e3d5f4e62b8d72eb6e399a
287	176	multiple-choice	<p>To reduce and control the spasticity there are possible options of Botox injection for this patient. What is the function of Botox injection in CP patient?</p>	f	100	0	2017-12-09 06:24:01	2017-12-10 11:44:07	3	eee144d5e5241e41430ac3979a30164a
288	177	multiple-choice	<p>Clinico Pathological Conference (CPC) meeting for a 25-year-old male with a lump of the right distal femur give inconclusive findings on the FNAB. The meeting suggests open biopsy for further examination. Which of this principle is suggested for doing the open biopsy?</p>	t	100	0	2017-12-09 06:27:25	2017-12-09 06:27:25	3	53ac21accf87e05af499c1c9404c55c2
289	178	multiple-choice	<p>A 15-year-old boy is diagnosed with osteosarcoma of the left distal femur Enneking stage IIB. Which condition is suitable for this diagnosis?</p>	t	100	0	2017-12-09 06:29:51	2017-12-09 06:29:51	3	87d538a03ac106ec8554ec87d19f19d4
290	179	multiple-choice	<p>A 21-year-old male patient comes to the clinic with enlarging mass of the distal third of the left femur. Radiological evaluation showed that the mass is situated with more involvement of the anterior surface. Pathology anatomy finding gives chondroid differentiation with high-grade poor differentiation of cellular pleomorphism with malignant osteoid. Based on the above description which is the suitable diagnosis for this patient.</p>	t	100	0	2017-12-09 06:32:38	2017-12-09 06:32:38	3	1fd4e86872b6499dd6d671348dbbe78e
291	169	multiple-choice	<p>Which of the following is the most appropriate treatment of this patient?</p>	t	100	0	2017-12-09 09:00:46	2018-01-04 04:19:55	3	1726a293fb5293b06b17ea2e755e62a8
292	169	multiple-choice	<p>If replantations were to have been attempted, and the finger remained viable postoperatively, what would be the most likely functional result?</p>	t	100	0	2017-12-09 09:00:46	2018-01-04 04:19:55	3	4b1937da1338ec86c531b34d6b863e72
293	180	multiple-choice	<p>Which of the following features is the strongest indicator of instability of this injury?</p>	t	100	0	2017-12-09 09:08:12	2017-12-09 09:08:12	3	6e7818378915eb82e5107cfe9286ccbe
294	180	multiple-choice	<p>Which of the following would be the best option for definitive treatment of this injury?</p>	t	100	0	2017-12-09 09:08:12	2017-12-09 09:08:12	3	9087069a074ad647f4ba878a995bc4d8
295	181	multiple-choice	<p>How is this fracture classified?</p>	t	100	0	2017-12-09 09:16:13	2017-12-09 09:16:13	3	d99ebc53de39afb8f49e14d3c5531e7d
296	181	multiple-choice	<p>What is the recommended treatment for this fracture?</p>	t	100	0	2017-12-09 09:16:13	2017-12-09 09:16:13	3	5eeed3d1f6e0b17c7a74f291e0782c40
297	182	multiple-choice	<p>What is the best indicator of end-organ perfusion in this patient?</p>	t	100	0	2017-12-09 23:24:53	2017-12-09 23:26:00	3	e87cf2c10661a3e73c528ec0a229bce6
298	182	multiple-choice	<p>What is the most appropriate management of his extremity injuries?</p>	t	100	0	2017-12-09 23:24:53	2017-12-09 23:26:00	3	793185d11a5a34ffad171874d617a32e
299	182	multiple-choice	<p>What complication has been shown to increase as the interval between external fixator conversion and internal fixation increases?</p>	t	100	0	2017-12-09 23:24:53	2017-12-09 23:26:00	3	edd1325dce9bb0fbdcfadce107388ca9
300	183	multiple-choice	<p>What is the most appropriate next step in management?</p>	t	100	0	2017-12-09 23:32:23	2018-01-04 04:13:57	3	5ea5205f73f9bb42ecab12af5b279146
998	523	essay	<p>What does the arthroscopic picture show? (20)</p>	f	100	0	2018-12-17 02:58:39	2018-12-17 02:58:39	3	f0abccd3b13662784d24455f5249217c
301	183	multiple-choice	<p>The patient is brought to the operating room and dishwater like fluid is drained from the wound. The fascial planes are easily separated with blunt palpation. Tissue cultures are likely to show what type of bacteria?</p>	t	100	0	2017-12-09 23:32:23	2018-01-04 04:13:57	3	222ff0727917ab3ff76cd88763155b36
302	183	multiple-choice	<p>Which of the following laboratory values is not associated with a diagnosis of soft tissue necrotizing infection?</p>	t	100	0	2017-12-09 23:32:23	2018-01-04 04:13:57	3	00a955af28e36c83983bc145807ef8a2
303	183	multiple-choice	<p>24 hours after the initial debridement, the patient has a dorsal hand wound measuring 5 &times; 4 cm with exposed tendon. His white blood count has decreased from 25,000/cc to 17,000/cc. His temperature is 38 degrees, heart rate is 88 bpm, and blood pressure is 100/64. What is the most appropriate next step in management?</p>	t	100	0	2017-12-09 23:32:23	2018-01-04 04:13:57	3	0218d27ef9e5827059e1ebceda1c815e
310	188	multiple-choice	<p>The early pulmonary complication such as atelectatis or pneumonia &nbsp;in SCI are mostly caused by</p>	f	100	0	2017-12-10 00:03:26	2017-12-26 08:31:05	3	0c212382eab676ccbdb57a92cfe5536f
313	191	essay	<p>Please describe the x-ray findings! (20)</p>	f	100	0	2017-12-10 02:55:53	2017-12-10 03:01:08	3	8e51bcfb86b8402b33b94418337b3d74
314	191	essay	<p>What is the diagnosis? (20)</p>	f	100	0	2017-12-10 02:55:53	2017-12-10 03:01:08	3	bb3ddc0e1e40c23e32346ad5eaa21334
315	191	essay	<p>In this case what structures commonly injured? (30)</p>	f	100	0	2017-12-10 02:55:54	2017-12-10 03:01:08	3	e8ce0dddf348bb906958e1987444d3b9
316	191	essay	<p>Please describe initial and definitive management! (30)</p>	f	100	0	2017-12-10 02:55:54	2017-12-10 03:01:08	3	ea11194b4eaf5024f2e33ef05496d1f1
317	192	essay	<div>1.What is the diagnosis? (20)</div>	f	100	0	2017-12-10 03:00:32	2018-01-04 04:37:09	3	6acd5884dff7da64a526d1678c45f53c
318	192	essay	<p>List 3 reason for open treatment? (40)</p>	f	100	0	2017-12-10 03:00:32	2018-01-04 04:37:09	3	92aa6b353593dc33ca2c87ff944638cc
319	192	essay	<p>List 3 possible complications after operation? (40)</p>	f	100	0	2017-12-10 03:00:32	2018-01-04 04:37:09	3	f7db3b643c20c6dc7380d282f58b9e1a
320	193	essay	<div>1.What is the diagnosis (20)</div>	f	100	0	2017-12-10 03:05:30	2017-12-10 03:05:30	3	da94dff3179d3f4b7b57ac3e05a7ebfc
321	193	essay	<p>What are the classic signs of this condition (30)</p>	f	100	0	2017-12-10 03:05:30	2017-12-10 03:05:30	3	d18c30cae561762c73d720667d0e3f50
322	193	essay	<p>What are the treatments (20)</p>	f	100	0	2017-12-10 03:05:30	2017-12-10 03:05:30	3	bac0b5a4bc8be65e00de49f80934cfb0
323	193	essay	<p>List 3 possible complications (30)</p>	f	100	0	2017-12-10 03:05:30	2017-12-10 03:05:30	3	5b8817fb3c44836b2909b23355fc985e
324	194	essay	<div>What is the diagnosis (20)</div>	f	100	0	2017-12-10 03:12:04	2018-01-03 05:03:07	3	ec1945ef68e824782dfdecfd83f411d2
325	194	essay	<p>What is the classification? (30)</p>	f	100	0	2017-12-10 03:12:04	2018-01-03 05:03:07	3	abec02e5161861aaebe5006fd933d842
326	194	essay	<p>What is the treatment? (20)</p>	f	100	0	2017-12-10 03:12:04	2018-01-03 05:03:07	3	e677c0553ec045f4040a308cc257107c
327	194	essay	<p>When surgery should be done and why? (30)</p>	f	100	0	2017-12-10 03:12:04	2018-01-03 05:03:07	3	367d51fd6eca3da11d79d83bb4d89382
328	195	essay	<div>What is the diagnosis? (30)</div>	f	100	0	2017-12-10 03:15:57	2017-12-10 03:16:25	3	acdc1306b91f7b4f8dffa6c9e85dffba
329	195	essay	<p>What is the treatment? (30)</p>	f	100	0	2017-12-10 03:15:57	2017-12-10 03:16:25	3	69346b494eeb0a767dd1beab1e123d30
330	195	essay	<p>When to consider surgical treatment and what procedure? (40)</p>	f	100	0	2017-12-10 03:15:57	2017-12-10 03:16:25	3	2a004b3c19fb2792450718afa10b09e2
331	196	essay	<p>Please describe the x-ray finding! (30)</p>	f	100	0	2017-12-10 03:29:58	2017-12-10 03:29:58	3	53eaeaaff53e3287656299218b3103ec
332	196	essay	<p>Please describe the histopathology finding! (25)</p>	f	100	0	2017-12-10 03:29:58	2017-12-10 03:29:58	3	b9d3984a905bd19d278999fbcdbf7ba3
333	196	essay	<p>What is the complete diagnosis? (25)</p>	f	100	0	2017-12-10 03:29:58	2017-12-10 03:29:58	3	1fd63d760c55386ebeb87e091968b218
334	196	essay	<p>What is the best surgical treatment for this pathology? (20)</p>	f	100	0	2017-12-10 03:29:58	2017-12-10 03:29:58	3	abb79bcd7d7e84e02b5894134010357f
335	197	essay	<p>What is the diagnosis? (25)</p>	f	100	0	2017-12-10 03:39:11	2018-01-04 04:45:13	3	45c807d3455d39d0ca95f4e5f32e8556
336	197	essay	<p>Please scoring this pathology using Mirel&rsquo;s score! (30)</p>	f	100	0	2017-12-10 03:39:11	2018-01-04 04:45:13	3	e531f73fd10a28bd66073488d769d601
337	197	essay	<p>What is the appropriate treatment for this pathology? (25)</p>	f	100	0	2017-12-10 03:39:11	2018-01-04 04:45:13	3	177509b91280275e4ad6e26d7b78e6a9
338	197	essay	<p>Please mention non-operative treatment for this pathology! (20)</p>	f	100	0	2017-12-10 03:39:11	2018-01-04 04:45:13	3	e92467a02875e64c9d681751eb23252d
343	199	essay	<p>Please describe the x-ray features! (40)</p>	f	100	0	2017-12-10 03:46:41	2017-12-10 03:58:00	3	f927e3ca57e17aa246392fbfbb6f107b
344	199	essay	<p>Please mention your differential diagnosis! (30)</p>	f	100	0	2017-12-10 03:46:41	2017-12-10 03:58:00	3	c3d65a5d88f63e69c2ce9bb35f124e3a
345	199	essay	<p>What is the best treatment? (30)</p>	f	100	0	2017-12-10 03:46:41	2017-12-10 03:58:00	3	a1d64b0f2ed8802cf5710ac016c70229
349	200	essay	<p>What is your differential diagnosis based on the figure 4b? (20)</p>	f	100	0	2017-12-10 04:05:16	2018-01-03 23:27:37	3	09f5070170c754a14405fd023b769a54
350	201	essay	<p>Please describe the X-ray finding! (20)</p>	f	100	0	2017-12-10 05:16:42	2017-12-10 05:16:42	3	603a365fc875011837354c3c42f8a7c2
351	201	essay	<p>Please describe the MRI finding! (20)</p>	f	100	0	2017-12-10 05:16:42	2017-12-10 05:16:42	3	218c05ab870ef5f15999e14f609b3730
352	201	essay	<p>What is your diagnosis? (30)</p>	f	100	0	2017-12-10 05:16:42	2017-12-10 05:16:42	3	3a1c3fbb4e9ffd4a7543df29dd639492
353	201	essay	<p>What is the best treatment for this pathology? (30)</p>	f	100	0	2017-12-10 05:16:42	2017-12-10 05:16:42	3	7cac4cfb4d62dcece8a9a31ae50a4f1e
354	202	essay	<p>Please describe abnormalities that you expecting find in clinical examination! (40)</p>	f	100	0	2017-12-10 05:22:49	2018-01-03 05:57:25	3	d91b0763566ff117244b36b045eb721d
355	202	essay	<p>Which radiographic measurement techniques are currently used to determine prognosis? (30)</p>	f	100	0	2017-12-10 05:22:49	2018-01-03 05:57:25	3	9d165607d55291c4eef0f9e71d5549ed
356	202	essay	<p>Please mention your differential diagnosis! (30)</p>	f	100	0	2017-12-10 05:22:49	2018-01-03 05:57:25	3	b1fa7e0bbbabc06dab375d35f40c5b19
357	203	essay	<p>Please describe what you see in MRI! (35)</p>	f	100	0	2017-12-10 05:34:58	2018-01-03 05:55:07	3	b2dfdaa3a76b801b3509e9d0619637c8
358	203	essay	<p>Please describe the position of X-ray to prove your diagnosis! (30)</p>	f	100	0	2017-12-10 05:34:58	2018-01-03 05:55:07	3	ce2c494db5814acd58e4d8703a2d92fd
359	203	essay	<p>How these abnormalities classified? (35)</p>	f	100	0	2017-12-10 05:34:58	2018-01-03 05:55:07	3	72c1127fe02afe5e24d3b01ba5aabf15
360	204	essay	<p>Please describe orthopedic appliances use in his hand! (30)</p>	f	100	0	2017-12-10 05:39:49	2018-01-03 06:01:01	3	63010a252356b1ff2947107113c70a32
361	204	essay	<p>Please define the zones of injury of the flexor tendon in the hand and wrist! (40)</p>	f	100	0	2017-12-10 05:39:49	2018-01-03 06:01:01	3	afc606d9279f84e6066c02560c7d541d
362	204	essay	<p>What is a jersey finger? (30)</p>	f	100	0	2017-12-10 05:39:49	2018-01-03 06:01:01	3	3dfbbeb6b277c0d69aaf8658e19ba301
363	205	essay	<p>Please describe the possibilities of structures affected! (35)</p>	f	100	0	2017-12-10 05:43:18	2017-12-10 05:43:18	3	f6ed05822696264e70f4047f24472f07
364	205	essay	<p>Which roots involves primarily in Erb&rsquo;s palsy? (35)</p>	f	100	0	2017-12-10 05:43:18	2017-12-10 05:43:18	3	9776b96f210e5d2a96c1aafcc29f925c
365	205	essay	<p>What is the characteristic positioning of the shoulder in Erb&#39;s palsy? (30)</p>	f	100	0	2017-12-10 05:43:18	2017-12-10 05:43:18	3	e218f5686175e6cbac274309509d1d25
366	206	essay	<p>How do you call this procedure? (35)</p>	f	100	0	2017-12-10 05:49:07	2017-12-10 05:49:07	3	534fbe65e6a0a1a53c493642f4f5e243
367	206	essay	<p>What structure to be examined? (35)</p>	f	100	0	2017-12-10 05:49:07	2017-12-10 05:49:07	3	b278bed70fcbaefb1fe604961de29523
368	206	essay	<p>What a problem if the structured was torn? (30)</p>	f	100	0	2017-12-10 05:49:07	2017-12-10 05:49:07	3	68f4e61618d21deaba8984b03273c281
369	207	multiple-choice	<p>What is your clinical diagnosis?</p>	t	100	0	2017-12-24 10:45:42	2018-01-04 04:18:57	3	4ed06e1a2dd8ca05024a01dfa8cd33e7
370	207	multiple-choice	<p>What is your plan to manage this patient?</p>	t	100	0	2017-12-24 10:45:42	2018-01-04 04:18:57	3	1dd59369ac3384f588c731721e679026
371	207	multiple-choice	<p>What is the problem that may happen during follow up in this type of case</p>	t	100	0	2017-12-24 10:45:42	2018-01-04 04:18:57	3	ce7ecbdebfc3fdceb2d001455e9fe684
372	207	multiple-choice	<p>Lateral condyle fracture is notorious for further displacement during fracture healing because</p>	t	100	0	2017-12-24 10:45:42	2018-01-04 04:18:57	3	4adedc922082ad08e1df6f3d100b990c
373	208	multiple-choice	<p>How do you manage this problem?</p>	t	100	0	2017-12-24 10:55:31	2017-12-24 10:55:31	3	849970847c1fecc4bee669261c87b69e
374	208	multiple-choice	<p>What structure may also injure in a humeral birth fracture?</p>	t	100	0	2017-12-24 10:55:31	2017-12-24 10:55:31	3	39e0aa6b7317c6903da87ca904e053c6
375	208	multiple-choice	<p>After 2 weeks of immobilization, the elbow and fingers are still not moving. What is your plan for this condition</p>	t	100	0	2017-12-24 10:55:31	2017-12-24 10:55:31	3	beceb18670883fd2d8387d26b7bca3cd
376	209	multiple-choice	<p>What is your plan for diagnostic work up</p>	f	100	0	2017-12-24 11:10:47	2017-12-26 09:06:57	3	50f47c2662e00004731777c947944dfc
377	209	multiple-choice	<p>The intravenous fluid access was on right ankle however the pathology is on the left knee. How do you explain this phenomenon?</p>	f	100	0	2017-12-24 11:10:47	2017-12-26 09:06:57	3	707e43b0c26a5a4b699ad39456d77f51
378	209	multiple-choice	<p>How do you manage the problem?</p>	f	100	0	2017-12-24 11:10:47	2017-12-26 09:06:57	3	8187b5d6b8aa8ea36965a45f81c3af56
379	210	multiple-choice	<p>The fibula proximal to the lesion is widened and the cortex is thickened. This is due to</p>	f	100	0	2017-12-24 11:30:02	2017-12-26 10:10:53	3	4d7043e9edbd23fc8b517d0852e075b8
380	210	multiple-choice	<p>The most important diagnostic work up to overcome this case is</p>	f	100	0	2017-12-24 11:30:02	2017-12-26 10:10:53	3	6efc63034077175147f4005d4d68f83c
381	210	multiple-choice	<p>What is your plan to manage this case?</p>	f	100	0	2017-12-24 11:30:02	2017-12-26 10:10:53	3	d0db68c06794fc5a04aa171b1c184022
390	211	multiple-choice	<p>The diagnosis is supracondylar fracture Gartland II based on the radiological appearance of</p>	t	100	0	2017-12-24 11:51:57	2017-12-24 11:52:43	3	8cd2b8bd7d5177ac5c6f66bfe2457381
391	211	multiple-choice	<p>How do you confirm that there is a displacement?</p>	t	100	0	2017-12-24 11:51:57	2017-12-24 11:52:43	3	15c36db0d0cc0befa5f15ba1c16e98cf
392	211	multiple-choice	<p>How do you manage this problem?</p>	t	100	0	2017-12-24 11:51:57	2017-12-24 11:52:43	3	d9946a81665400915f1d33755674854d
393	212	multiple-choice	<p>A 12-year-old boy with a femoral fracture is planned to undergo closed reduction and stabilization using Titanium elastic nail. Upon measurement, the isthmus is 12 mm. What diameter of nail that is the best for that size</p>	f	100	0	2017-12-24 12:00:46	2017-12-26 09:08:28	3	12062da8ce3b1241c786208b2bcc9e80
394	213	multiple-choice	<p>A 5 years old girl had a left femoral fracture. She has a superficial excoriation wound on the outer part of her left thigh, however, she is otherwise healthy.&nbsp;This case is not ideal for stabilization using Titanium elastic nail (TEN) because</p>	t	100	0	2017-12-24 12:07:13	2017-12-24 12:07:13	3	85209f9e695ee4af31ef6c30bd23b5a8
395	214	multiple-choice	<p>What is your diagnosis?</p>	t	100	0	2017-12-24 12:14:27	2017-12-24 12:14:27	3	20ea176977a22fb5aa9c2479976e5552
396	214	multiple-choice	<p>How do you manage this case?</p>	t	100	0	2017-12-24 12:14:27	2017-12-24 12:14:27	3	f5eab7c4f45eb6b9aeafa42ed04fb4f5
397	215	essay	<p>What is the pathophysiology that causes the scissoring gait? (40)</p>	f	100	0	2017-12-24 12:22:16	2017-12-24 13:47:02	3	b0c0a0bffa10ad44211b9dc8be962ef2
398	215	essay	<p>Please name the technique to measure the degree of hip subluxation in cerebral palsy! (30)</p>	f	100	0	2017-12-24 12:22:16	2017-12-24 13:47:02	3	1e8e30e2502523bba6d2cf37c408c953
399	215	essay	<p>The right hip is subluxated 20% whereas the left is subluxated 15%. How do you manage this problem surgically before proceed to stretching and physiotherapy? (30)</p>	f	100	0	2017-12-24 12:22:16	2017-12-24 13:47:02	3	d7d06f9a5b2a9d65d80891a8c98cee03
400	216	essay	<p>What is your diagnosis? (30)</p>	f	100	0	2017-12-24 12:26:09	2017-12-24 13:46:17	3	8669a6be6f118e67f76225d6d77eed62
401	216	essay	<p>Do you think this is the stable type or unstable type, please explain! (40)</p>	f	100	0	2017-12-24 12:26:09	2017-12-24 13:46:17	3	adcda78f9d8624e9f2f058e3cc0f0d14
402	216	essay	<p>Please name 3 findings on physical examination in this case! (30)</p>	f	100	0	2017-12-24 12:26:09	2017-12-24 13:46:17	3	5e053a98a184a03a685c3c4f05375e32
403	217	essay	<p>What is your clinical diagnosis and classification? (30)</p>	f	100	0	2017-12-24 13:25:58	2017-12-24 13:45:53	3	a33e6be4eb28da91dee748b264a11986
404	217	essay	<p>Please describe how do you measure Metaphyseal Diaphyseal Drennan angle! (40)</p>	f	100	0	2017-12-24 13:25:58	2017-12-24 13:45:53	3	f0e4f4468453bc73d2b1e53f850dd363
405	217	essay	<p>How do you manage this problem? (30)</p>	f	100	0	2017-12-24 13:25:58	2017-12-24 13:45:53	3	460607b01daa7c3650e22948bffb6c48
406	218	essay	<p>How do you manage this problem? (40)</p>	f	100	0	2017-12-24 13:44:56	2017-12-24 13:44:56	3	41d29e6207aabf09c0fdc5852ac79d08
407	218	essay	<p>After seeing the x-ray, the parents ask you to explain what is your plan to correct the deformity and restore the function? (60)</p>	f	100	0	2017-12-24 13:44:56	2017-12-24 13:44:56	3	a45c36f85b7f72384e4429adfd89b630
408	219	essay	<p>What is the most likely working diagnosis? (20)</p>	f	100	0	2017-12-24 13:53:30	2018-01-03 06:28:19	3	492aca3a104e18f23df01ff807b92f85
409	219	essay	<p>The disease mentioned in your previous answer is caused by (20)</p>	f	100	0	2017-12-24 13:53:30	2018-01-03 06:28:19	3	49d0916e11c5ada578244be8e0177c11
410	219	essay	<p>The problem mentioned in the previous answer &nbsp;is caused by mutation pin 2 gene of (20)</p>	f	100	0	2017-12-24 13:53:30	2018-01-03 06:28:19	3	b4d76493f295389028141ec9b6680bc9
411	219	essay	<p>How do you manage the fractures? (20)</p>	f	100	0	2017-12-24 13:53:30	2018-01-03 06:28:19	3	4929e02839e6d90a61f47327d8e52122
412	219	essay	<p>What is the medicine that may increase the quality of bones in this case? (20)</p>	f	100	0	2017-12-24 13:53:30	2018-01-03 06:28:19	3	8c2d5a676428e2c0973aa8efff773071
413	221	multiple-choice	<p>What is your clinical diagnosis</p>	f	100	0	2017-12-27 10:42:21	2017-12-27 11:18:47	3	7d1bea69827930e4446bf188b82c62e3
414	221	multiple-choice	<p>What is your plan to manage this problem</p>	f	100	0	2017-12-27 10:51:28	2017-12-27 11:18:47	3	12ed232f1c3c9261e8878f8460e99b5d
415	221	multiple-choice	<p>What is the problem that may happen during follow up in this type of case</p>	f	100	0	2017-12-27 10:51:28	2017-12-27 11:18:47	3	efe461deb971cd60e2340c98cb963823
416	221	multiple-choice	<p>Lateral condyle fracture is notorius for further displacement during fracture healing because</p>	f	100	0	2017-12-27 10:51:28	2017-12-27 11:18:47	3	05d64c57194ccb29846cfd0360c648d7
423	225	essay	<p>Please describe the MRI finding ! (25)</p>	f	100	0	2017-12-27 13:42:12	2017-12-27 16:12:47	3	835ffcf872e95bcf581f4ac39f89ab5b
424	225	essay	<div>What is the diagnosis ?&nbsp;(25)</div>	f	100	0	2017-12-27 13:42:12	2017-12-27 16:12:47	3	cd684ba9c712aeac45bd233d8a071d94
425	225	essay	<div>How do you manage this patient ?&nbsp;(25)</div>	f	100	0	2017-12-27 13:42:12	2017-12-27 16:12:47	3	1a119342ad17797d90fb8bedcb277342
426	225	essay	<div>Please describe type of graft option for this reconstruction surgery?&nbsp;(25)</div>	f	100	0	2017-12-27 13:42:12	2017-12-27 16:12:47	3	98c129a37efe926e0781ebdb887da5ec
427	226	essay	<div>What is your diagnosis?&nbsp;(25)</div>	f	100	0	2017-12-27 13:45:01	2017-12-27 16:14:11	3	cd7cc8821948424672ca3ff89e1a8e82
428	226	essay	<div>Explain the pathomechanism!&nbsp;(25)</div>	f	100	0	2017-12-27 13:45:01	2017-12-27 16:14:11	3	210c706e1369b38c3a311fc4797fb8c2
429	226	essay	<div>Please describe the classification of this disease?&nbsp;(25)</div>	f	100	0	2017-12-27 13:45:01	2017-12-27 16:14:11	3	e7f9edb3021656a57181ca68dea6f379
430	226	essay	<div>Please mention the risk factors for this disease?&nbsp;(25)</div>	f	100	0	2017-12-27 13:45:01	2017-12-27 16:14:11	3	cc0b758a7a30892a56e180ecd296cf16
431	227	essay	<div>Please describe the clinical and x-ray findings?&nbsp;(25)</div>	f	100	0	2017-12-27 13:49:32	2017-12-27 16:14:44	3	623342da7c32bc12e2af862c8aa29077
432	227	essay	<div>What are possible clinical diagnosis?&nbsp;(25)</div>	f	100	0	2017-12-27 13:49:32	2017-12-27 16:14:44	3	4747e6f2015ca73ed9632fe9d86df18e
433	227	essay	<div>What are the complications ?&nbsp;(25)</div>	f	100	0	2017-12-27 13:49:32	2017-12-27 16:14:44	3	734f4e01ec51b1212b3f4a884b61486a
434	227	essay	<div>Please describe the reduction techique?&nbsp;(25)</div>	f	100	0	2017-12-27 13:49:32	2017-12-27 16:14:44	3	b7159bbf4e99a1c9f5becfc5dab4005c
435	228	essay	<div>What structure are prevent flexor tendon from retracting proximally when ruptured? (35)</div>	f	100	0	2017-12-27 13:53:37	2017-12-27 13:53:37	3	861cb26fa25d9ec32f98b0d94ee4325e
436	228	essay	<div>What is the source of flexor tendon nutrition? (35)</div>	f	100	0	2017-12-27 13:53:38	2017-12-27 13:53:38	3	6ae7122265340146b81754720e7c2822
437	228	essay	<div>3 months after tendon repair, he complaining that the adjacent finger cannot full flexed when&nbsp; grasping while the repaired finger can fully flexed, what is the problem called? (30)</div>	f	100	0	2017-12-27 13:53:38	2017-12-27 13:53:38	3	9885a744426939d0e787fefc8761dacc
438	229	essay	<div>How to do closed reduction? (25)</div>	f	100	0	2017-12-27 14:14:15	2017-12-27 14:14:15	3	2743b9408f722330b07ece2fb47293cf
439	229	essay	<div>List 3 Indication for operation? (25)</div>	f	100	0	2017-12-27 14:14:15	2017-12-27 14:14:15	3	2fea709175c92834e90a19743d228aec
440	229	essay	<div>List 2 operative management option? (25)</div>	f	100	0	2017-12-27 14:14:15	2017-12-27 14:14:15	3	3da2ea23db69efac57d7450a578632d6
441	229	essay	<div>What is the acceptable angulation for 2nd-5th metacarpal neck fracture? (25)</div>	f	100	0	2017-12-27 14:14:15	2017-12-27 14:14:15	3	fc7e1abc8e7dec67aed108f6d6ba6cc2
442	230	essay	<div>What is the the most possible diagnosis of the previous injury (20)</div>	f	100	0	2017-12-27 14:16:31	2017-12-27 14:16:31	3	0051deee2c01cfbd125d7fd54751f0d6
443	230	essay	<div>What is the pathoanatomy of point 1? (20)</div>	f	100	0	2017-12-27 14:16:31	2017-12-27 14:16:31	3	96a3fd323efa5677d7fc4b3e18ffd3cc
444	230	essay	<div>What is the deformity now? (20)</div>	f	100	0	2017-12-27 14:16:31	2017-12-27 14:16:31	3	b5e189c5050adfa43d08d5b64c7450a1
445	230	essay	<div>List 3 pathoanatomy of point 3 in this case ? (40)</div>	f	100	0	2017-12-27 14:16:31	2017-12-27 14:16:31	3	2838f861d844ce9eff1d671d110b77ca
447	231	essay	<div>Describe about at least 3 tumor marker (35)</div>	f	100	0	2017-12-27 14:24:46	2017-12-27 14:27:31	3	d34d0026cd47447dec51c9c7402c79ac
448	231	essay	<div>Describe about &ldquo;seed and soil&rdquo; theory (30)</div>	f	100	0	2017-12-27 14:24:46	2017-12-27 14:27:32	3	ef83f0796473e5368449155e89f7bb02
449	232	essay	<div>What is the step of Greenspan for read the lession in extremity (35)</div>	f	100	0	2017-12-27 14:27:17	2018-07-18 11:08:19	3	037caa7e201c3a68b00cf4a1e1ca72e6
450	232	essay	<div>What is the possible diagnosis according to the x-ray and&nbsp; explain why (30)</div>	f	100	0	2017-12-27 14:27:17	2018-07-18 11:08:19	3	ceb0fa71d8c3214d73ffbafce4abfe33
451	233	essay	<div>Describe about surgical margin in musculoskeletal tumor (30)</div>	f	100	0	2017-12-27 14:29:59	2017-12-27 14:29:59	3	e9770da5006ba6c06f21a7c4b558de4c
452	233	essay	<div>Which type of surgical margin related to the picture above and what is the possible diagnosis (35)</div>	f	100	0	2017-12-27 14:29:59	2017-12-27 14:29:59	3	8042cccab6d9751b05784406cbae44ab
453	233	essay	<p>How do you manage surgically&nbsp; according to your diagnosis (35)</p>	f	100	0	2017-12-27 14:29:59	2017-12-27 14:29:59	3	3585e24a83ad83a3ee77e7a7f35edffa
454	234	multiple-choice	<p>Which of the following is not a physical examination finding in biceps tendon pathology?</p>	f	100	0	2017-12-27 14:43:37	2017-12-27 15:00:38	3	1d2c0376f7ba4b27f07a981e559124ac
455	234	multiple-choice	<p>If the above patient is clinically diagnosed with biceps tendonitis, what is the preferred initial management?</p>	t	100	0	2017-12-27 14:43:37	2017-12-27 15:00:38	3	47f6ca5c0a187110ed2907e74922e09c
456	235	multiple-choice	<p>Where does this tendon principally insert?</p>	t	100	0	2017-12-27 14:57:58	2017-12-27 14:57:58	3	f60ec7cbff02baa60efedaacb079ada6
457	235	multiple-choice	<p>What is the anatomical peculiarity of the ECU?</p>	t	100	0	2017-12-27 14:57:58	2017-12-27 14:57:58	3	901e3598129a40b3279f73c0ab8e4c64
458	235	multiple-choice	<p>In addition to acting as a wrist extensor, what other role has been attributed to the ECU?</p>	t	100	0	2017-12-27 14:57:58	2017-12-27 14:57:58	3	2480779d7047748ca663edf157d0b7fd
459	234	multiple-choice	<p>Which of the following is not an indication for surgical intervention with long head of the biceps tendon pathology?</p>	f	100	0	2017-12-27 15:00:38	2017-12-27 15:00:38	3	fb3082c38a350815573e51c1dd599be4
460	236	multiple-choice	<p>Based on this history and radiographic examination, how should you advise the family?</p>	t	100	0	2017-12-27 15:27:25	2017-12-27 15:27:25	3	0cfe2f51daf60e05ddf345e72ed18107
461	236	multiple-choice	<p>What is the most likely underlying bone problem?</p>	f	100	0	2017-12-27 15:27:25	2017-12-27 15:27:25	3	73f98474c845710966bd76d32bfc01fa
462	236	multiple-choice	<p>The fracture location and pattern can be explained because</p>	t	100	0	2017-12-27 15:27:25	2017-12-27 15:27:25	3	ebf37034d4f892a981a58accda9d3f87
463	237	multiple-choice	<p>The most appropriate treatment at this time would be which of the following?</p>	f	100	0	2017-12-27 15:35:43	2017-12-27 15:35:43	3	815f23205f42942277cb291e19bd5feb
464	237	multiple-choice	<p>The most likely complication after this fracture is likely to be which of the following?</p>	t	100	0	2017-12-27 15:35:43	2017-12-27 15:35:43	3	5905a7991efa9a43f62de9ef1ab98349
465	238	essay	<p>What is your diagnosis and please classify the fracture (25)</p>	f	100	0	2017-12-27 16:02:32	2017-12-27 16:02:32	3	676361b1343aec98f10da71b999d8cb6
466	238	essay	<p>How do you manage this problem (25)</p>	f	100	0	2017-12-27 16:02:32	2017-12-27 16:02:32	3	3426d76df328975e2e1da6d2cbf49f5d
467	238	essay	<p>If your answer on no 2 is surgical intervention, what approach do you use and why? (25)</p>	f	100	0	2017-12-27 16:02:32	2017-12-27 16:02:32	3	4f09f62d61731d47a74c7681de1622db
468	238	essay	<p>What complication may occur in the future &nbsp;(25)</p>	f	100	0	2017-12-27 16:02:32	2017-12-27 16:02:32	3	c2337bf105acafbbaff142e49bc785d8
469	239	essay	<p>Please mention three (3) differential diagnosis that may produce such deformities (40)</p>	f	100	0	2017-12-27 16:04:45	2017-12-27 16:04:45	3	0192e0a8c7745c565f47a2f912b07ae4
470	239	essay	<p>An idiopathic clubfoot is best managed by Ponseti protocol. Could you describe in brief the protocol of Ponseti (60)</p>	f	100	0	2017-12-27 16:04:45	2017-12-27 16:04:45	3	833e3158e0905c80e31bd049164b882c
471	240	essay	<p>What is your diagnosis (40)</p>	f	100	0	2017-12-27 16:06:33	2017-12-27 16:06:33	3	2ed42c520e2a88559976ce12de692b57
472	240	essay	<p>How do you manage this problem (60)</p>	f	100	0	2017-12-27 16:06:33	2017-12-27 16:06:33	3	3c4eb1c6bcdff36e597be92a39bc9dc1
473	241	essay	<div>What is the mode of injury ? (25)</div>	f	100	0	2017-12-27 16:08:47	2017-12-27 16:08:47	3	0e5ae220ba553a30cedf352b908d17d1
474	241	essay	<div>What is your diagnosis ? (25)</div>	f	100	0	2017-12-27 16:08:47	2017-12-27 16:08:47	3	ba587573d76ed0a3a5428626870a1b6b
475	241	essay	<div>What is the most possible complication of this type of injury ? (25)</div>	f	100	0	2017-12-27 16:08:47	2017-12-27 16:08:47	3	a25cd844fc61f279fbc3f7a35d993581
476	241	essay	<div>What is your best choice for treatment ? (25)</div>	f	100	0	2017-12-27 16:08:47	2017-12-27 16:08:47	3	96f7181f544c7c19b3271864ea2366f1
477	242	essay	<div>Please describe the abnormal radiologic finding at L1 ? (25)</div>	f	100	0	2017-12-27 16:10:24	2017-12-27 16:10:24	3	2291cdadc0135dd5bdc97054a13f1524
478	242	essay	<div>What is your diagnosis&nbsp; ? (25)</div>	f	100	0	2017-12-27 16:10:24	2017-12-27 16:10:24	3	5925bd356c9e445110cb45a01f8a14b0
479	242	essay	<div>Explain biomechanically, why the kyphotic deformity tend to progress in severe osteoporotic spine ? (25)</div>	f	100	0	2017-12-27 16:10:24	2017-12-27 16:10:24	3	30d3fd103f6040babeb26256b7612646
480	242	essay	<div>What is&nbsp; AAOS&nbsp; Clinical Practical&nbsp; Guidance recommendation&nbsp; about vertebroplasty ? (25)</div>	f	100	0	2017-12-27 16:10:24	2017-12-27 16:10:24	3	a3d9f3fd9a7d88ee5bdc0feb4563db77
481	243	essay	<div>Please describe at least 2 abnormality finding in MRI!&nbsp;(40)&nbsp;&nbsp;</div>	f	100	0	2017-12-27 16:11:59	2017-12-30 22:28:44	3	4aa12d0d6d12e7e932e653707ac6d1aa
482	243	essay	<div>Why the kyphotic deformity happened? (30)</div>	f	100	0	2017-12-27 16:11:59	2017-12-30 22:28:44	3	6fb9aba92bf50a5f44e929e41964b012
483	243	essay	<div>What is your diagnosis ? (30)</div>	f	100	0	2017-12-27 16:11:59	2017-12-30 22:28:44	3	2016b75b651ff9b8e173293c08792bae
484	244	essay	<p>What is your diagnosis?&nbsp;(25)</p>	f	100	0	2017-12-27 16:24:48	2018-01-03 06:50:45	3	831ae7de7b0efbc2e271b7e602827dab
485	244	essay	<p>Please explain treatment for this case ?&nbsp;(25)</p>	f	100	0	2017-12-27 16:24:48	2018-01-03 06:50:45	3	1606d8145be69d98bae611a20f7dc4a5
486	244	essay	<p>Describe rehabilitation program for this patient? (25)</p>	f	100	0	2017-12-27 16:24:48	2018-01-03 06:50:45	3	3b9693ec3149084f4658d5aff69c96fc
487	244	essay	<p>What is the complications of your treatment? (Immediate, Early, Late) (25)</p>	f	100	0	2017-12-27 16:24:48	2018-01-03 06:50:45	3	8bf68e044ad70c88ab1ad8f1f7b2cdbe
488	245	essay	<p>What is your diagnosis and please describe 4 things to support your diagnosis? (30)</p>	f	100	0	2017-12-27 16:28:51	2017-12-30 22:06:41	3	300a5938384de83860425e4c08a636f9
489	245	essay	<p>Please describe How do you treat this case? (25)</p>	f	100	0	2017-12-27 16:28:51	2017-12-30 22:06:41	3	bc98f3dd7d7f0b5b799e3ddb3128f20a
490	245	essay	<p>Please explain the rehabilitation program for this case ? (25)</p>	f	100	0	2017-12-27 16:28:51	2017-12-30 22:06:41	3	bc982604f396ccad97f2f987ee608661
491	245	essay	<p>What are the complications of your treatment? (20)</p>	f	100	0	2017-12-27 16:28:51	2017-12-30 22:06:41	3	484f12db8353985c6adf1da79aa2b31a
492	246	essay	<div>What is your complete diagnosis? (According to Vancouver Classification) (25)</div>	f	100	0	2017-12-27 16:38:39	2017-12-30 21:58:20	3	ea7b9b62b97ca7505bf4017829672815
493	246	essay	<div>Please explain treatment for this patient?&nbsp;(25)</div>	f	100	0	2017-12-27 16:38:39	2017-12-30 21:58:20	3	691886c9fbaeb197e32ca1f08a00b194
494	246	essay	<div>Please describe the rehabilitation programs?&nbsp;(25)</div>	f	100	0	2017-12-27 16:38:39	2017-12-30 21:58:20	3	47ae9eeb0b464a391d789635b2089ff7
495	246	essay	<div>What is the complications might happen after the treatment?&nbsp;(25)</div>	f	100	0	2017-12-27 16:38:39	2017-12-30 21:58:20	3	26857f1520d1916dbfa6408e8f1973d1
496	247	essay	<div>What is your diagnosis? (20)</div>	f	100	0	2017-12-27 16:47:36	2017-12-30 21:56:08	3	1bf6d290bfcc2ea9d49cedef850bbb4b
497	247	essay	<div>How do you treat the patient ?&nbsp;(20)</div>	f	100	0	2017-12-27 16:47:36	2017-12-30 21:56:08	3	2d25c9d02daf439c5301aa00895ff85e
498	247	essay	<div>How do you program for rehabilitation?&nbsp;(20)</div>	f	100	0	2017-12-27 16:47:36	2017-12-30 21:56:08	3	7620081b5a00cc2315badaeb97a70b16
499	247	essay	<div>If the knee is grossly unstable, what do you suggest to the patient?&nbsp;(20)</div>	f	100	0	2017-12-27 16:47:36	2017-12-30 21:56:08	3	d6720af96defce639118f3a63ecfc20a
500	247	essay	<div>In the next 15 years the patient expects to get a better function of the knee, what will you do?&nbsp;(20)</div>	f	100	0	2017-12-27 16:47:36	2017-12-30 21:56:08	3	5bc6dd87bdc4e617a975329ecd9be4dd
501	248	essay	<div>What is your diagnosis?&nbsp;(20)</div>	f	100	0	2017-12-27 16:51:18	2017-12-30 21:44:45	3	9d01e6ad5bf7f9682c158056dee6d5db
502	248	essay	<div>How do you treat?&nbsp;(20)</div>	f	100	0	2017-12-27 16:51:18	2017-12-30 21:44:45	3	ad2d7683503cbe75827e148488adffec
503	248	essay	<div>What are the graft choices?&nbsp;(20)</div>	f	100	0	2017-12-27 16:51:18	2017-12-30 21:44:45	3	f51b237199936480b5164889157f1e09
504	248	essay	<div>How do you rehabilitate?&nbsp;(20)</div>	f	100	0	2017-12-27 16:51:18	2017-12-30 21:44:45	3	dcb0937d9c338144696fe2a602230ef1
505	248	essay	<div>What is the complications? (20)</div>	f	100	0	2017-12-27 16:51:18	2017-12-30 21:44:46	3	5e3d31e0b46e05e53ba3e5af3856e8bc
506	232	essay	<p>What the meaning of periosteal reaction (35)</p>	f	100	0	2017-12-28 08:41:47	2018-07-18 11:08:19	3	392ac1669153a553a13f875c07d243f7
507	249	essay	<p>Please mention the name of pain intervention! (25)</p>	f	100	0	2017-12-29 23:22:57	2017-12-30 21:34:42	3	f986a99240cdf1ac704f0dba11671e12
508	249	essay	<p>What is the level of intervention? (25)</p>	f	100	0	2017-12-29 23:22:57	2017-12-30 21:34:42	3	9adf38524a4d0554fc42da458adf793b
509	249	essay	<p>What is the type of joint base on binding tissue? (25)</p>	f	100	0	2017-12-29 23:22:57	2017-12-30 21:34:42	3	1bf7893cdf0f6995b8ec5e73f2a44528
510	249	essay	<p>What is the purpose of this intervention in diagnostic perspective? (25)</p>	f	100	0	2017-12-29 23:22:57	2017-12-30 21:34:42	3	7ad929c360f647c32d003e2937f31a3e
511	250	essay	<p>Please mention the&nbsp;spine pathology pointed by the yellow arrow! (25)</p>	f	100	0	2017-12-29 23:40:46	2018-01-04 04:54:14	3	9aaafdb0ebf614ae2e42b21e91ff94d4
512	250	essay	<p>What is the diagnosis? (25)</p>	f	100	0	2017-12-29 23:40:46	2018-01-04 04:54:14	3	60d3d3c8b2d6ab151817a000f60ea5dc
513	250	essay	<p>What (repetitive) movement can produce this abnormality? (25)</p>	f	100	0	2017-12-29 23:40:46	2018-01-04 04:54:14	3	9346b6cd03c0eb208b8309a463e877c2
514	250	essay	<p>Please describe the pathophysiology of this abnormality! (25)</p>	f	100	0	2017-12-29 23:40:46	2018-01-04 04:54:14	3	fbfdc0055e6b81ef0a21c109b65dc79c
515	251	essay	<p>Please mention the potential pain generator in LBP instead of SI Joint? (20)</p>	f	100	0	2017-12-29 23:45:20	2018-01-04 05:47:17	3	25bf3f52721fa29e3237365d3cf8bd9b
516	251	essay	<p>Please describe the pathophysiology of this abnormality! (20)</p>	f	100	0	2017-12-29 23:45:20	2018-01-04 05:47:17	3	3a23d9bbe51937dc4bd46046150a4f95
517	251	essay	<p>Please mention at least 2 provocative test for Si joint pain! (20)</p>	f	100	0	2017-12-29 23:45:20	2018-01-04 05:47:17	3	75aac9d79b7db4d31d28eb1f0e6f2bb8
518	251	essay	<p>What is the gold standard for diagnostic in SI joint pain? (20)</p>	f	100	0	2017-12-29 23:45:20	2018-01-04 05:47:17	3	b6ab863b9905bf74f41a8c2ad4811845
519	251	essay	<p>What kind of surgery will you do, if all the conservative treatment are failed? (20)</p>	f	100	0	2018-01-04 05:47:17	2018-01-04 05:47:17	3	757e20cd29876bc1c9ac98e91a67a609
520	252	multiple-choice	<p>Which gene or protein is the most specific marker of mature osteoblasts but is not expressed by immature, proliferating osteoblasts?</p>	t	100	0	2018-05-20 00:02:24	2018-05-20 00:02:24	3	7d338cb4b55617b2bc71491bfd175cf5
521	253	multiple-choice	<p>When discussing metal implants and devices, which of the following best describes fatigue?</p>	t	100	0	2018-05-20 00:04:33	2018-05-20 00:04:33	3	1e698568d10309f0ce84ccd0fbe95528
522	254	multiple-choice	<p>When using C-arm fluoroscopy, patient radiation exposure will be increased with which of the following?</p>	t	100	0	2018-05-20 00:06:54	2018-05-20 00:07:03	3	6fde902793c2f94e3c9ab58f4707dd1c
523	255	multiple-choice	<p>All of the following are advantages of a body-controlled prosthesis compared to a myoelectric prosthesis for patients with upper extremity amputations <strong>EXCEPT</strong></p>	f	100	0	2018-05-20 00:09:31	2018-05-20 00:09:31	3	995bfa6127217a6fae42bbef94115d40
524	256	multiple-choice	<p>A 63-year-old woman falls from standing and lands on her left hand. She complains of deformity and wrist pain. Radiographs are provided in the figure above. Following closed reduction, the patient inquires whether she has osteoporosis and if she is likely to have another fracture. In counseling the patient, which of the following is the strongest predictor for a future fracture from low energy trauma?</p>	t	100	0	2018-05-20 00:15:44	2018-05-20 00:15:44	3	f68faa671cd6b78ebb8d728ac1ca6c40
525	257	multiple-choice	<p>Which of the following is a mechanism by which low-intensity pulsed ultrasound is reported to stimulate fracture healing?</p>	f	100	0	2018-05-20 00:17:40	2018-05-20 00:17:40	3	784d097a32a9ac8d5fbf49cc792914bc
526	258	multiple-choice	<p>What description below best describes galvanic corrosion?</p>	t	100	0	2018-05-20 12:48:36	2018-05-20 12:48:36	3	445c2fcc5e65951d041b93f3e63b09e1
527	259	multiple-choice	<p>During the total hip arthroplasty, which of the following interventions increases the risk of pulmonary ventilation-perfusion mismatch the greatest?</p>	t	100	0	2018-05-20 12:56:23	2018-05-20 12:56:23	3	93c7e0c6d080a7a8b6f0babe7357be50
528	260	multiple-choice	<p>A healthy human knee normally contains approximately 2 milliliters of synovial fluid. What cell produces synovial fluid?</p>	t	100	0	2018-05-20 12:58:32	2018-05-20 12:58:32	3	f3e191be047fb4f818123c6187b0a940
529	261	multiple-choice	<p>A 20-year-old male is involved in a motor vehicle collision and sustains a depressed tibial plateau fracture. When performing surgery, if calcium sulfate is used as the primary bone substitute void filler, an increase in which of the following outcomes may be expected as compared to autograft?</p>	t	100	0	2018-05-20 13:01:48	2018-05-20 13:01:48	3	30284e7065a1c8527140af4de90b8d58
530	262	multiple-choice	<p>Which of the following osteoconductive bone graft substitutes resorbs faster than the rate at which bone growth occurs?</p>	t	100	0	2018-05-20 13:03:51	2018-05-20 13:03:51	3	77ea254406185529ae9d0ff3afca70c4
531	263	multiple-choice	<p>The figure above displays a schematic of the zones of articular hyaline cartilage. Which of the following zones has been shown to contain articular cartilage progenitor cells?</p>	t	100	0	2018-05-20 13:22:17	2018-05-20 13:22:17	3	ee0daaf786330293957c61f8240b17fa
920	484	multiple-choice	<p>A 73-years-old woman who return for her annual follow-up 14 years after undergoing total hip arthroplasty. The patient begins to experience pain, and a decision is made to proceed with surgical intervention. When performing a posterior approach to the hip, which structure protects the anterior retractor from causing damage to the femoral neurovascular structure?</p>	t	100	0	2018-12-04 18:53:17	2018-12-04 18:53:17	3	e38e59f7a3bd28e5ab62b3321beb8637
533	264	multiple-choice	<p>A 67-year-old female presented 2 months ago to her primary care physician with left-sided thigh pain. A radiograph was taken at that time and is shown in Figure A. She was diagnosed at that time with a quadriceps strain and given a prescription for ibuprofen and physical therapy. She is now in the emergency room with severe left thigh pain and inability to bear weight on the left lower extremity after bending down to tie her shoes. She denies any constitutional symptoms. A current radiograph from the emergency room is shown in Figure B. Which of the following most likely explains this patient&#39;s fracture?</p>	t	100	0	2018-05-20 13:28:37	2018-05-20 13:28:37	3	ee9e403f96c05138ad57ea52ec0719f9
534	265	multiple-choice	<p>Loss of function in the 25(OH) vitamin D1-alpha hydroxylase gene causes which of the following diseases?</p>	t	100	0	2018-05-20 13:31:45	2018-05-20 13:31:45	3	5c9a97ab1fe9ce20ad9e646adfd5ca79
535	266	multiple-choice	<p>Which of the following mediators reduces bone resorption?</p>	t	100	0	2018-05-20 13:33:19	2018-05-20 13:33:19	3	3f5e13f988621fff3d5fcabf1d9e2b19
536	267	multiple-choice	<p>Which of the following groups correctly identifies serologic tests that are required by the American Association of Tissue Banks (AATB) for musculoskeletal tissue allografts?</p>	t	100	0	2018-05-20 13:37:50	2018-05-20 13:37:50	3	5dfe4c878fa3c4efd58181318e0488da
537	268	multiple-choice	<p>A 27-year-old patient comes in for a new prescription for his below-knee amputation prosthesis because it is not fitting properly. All of the following are complaints and examination findings consistent with a prosthetic foot that is placed too far inset <strong>EXCEPT</strong>:</p>	t	100	0	2018-05-20 13:39:53	2018-05-20 13:39:53	3	625561e8609a8760fe3e0f966ca02f73
538	269	multiple-choice	<p>Which of the following study designs represent a level III evidence study?</p>	t	100	0	2018-05-20 13:42:05	2018-05-20 13:42:05	3	a16050613a9a4a790ea1144c354e9d70
539	270	multiple-choice	<p>The femur radiograph of a healthy 25-year-old female is compared to the femur radiograph of a healthy 85-year-old female. Which of the following best describes the 25-year-old&#39;s femur?</p>	t	100	0	2018-05-20 13:44:53	2018-05-20 13:44:53	3	d6f38f2371bbfb4aeeca106ae11ef02a
540	271	multiple-choice	<p>A typical load-elongation curve of a ligament is shown in Figure A. What region of the curve represents elastic deformation occurring after the crimped ligament fibrils have been straightened?</p>	t	100	0	2018-05-20 13:47:00	2018-05-20 13:47:00	3	9dfbe0ec8e36f64ab285c3ee5daa79b0
541	272	multiple-choice	<p>Using levels of evidence in research studies, which of the following represents a level II study?</p>	t	100	0	2018-05-20 13:48:53	2018-05-20 13:48:53	3	e6aa4499450bbd11cd3799ba737598fa
542	273	multiple-choice	<p>A 64-year-old female with rheumatoid arthritis has decreased functional use of the left hand for activities of daily living. On physical examination she has fixed deformities of the metacarpophalangeal (MCP) joints as demonstrated in Figure A. A radiograph is shown in Figure B. Which of the following management options for the finger MCP joints most likely lead to the least amount of extensor lag and improvement of the ulnar drift at 1-year followup?</p>	t	100	0	2018-05-20 13:56:46	2018-05-20 13:56:46	3	b1e86f358377c6719eb8987617345cab
543	274	multiple-choice	<p>Which of the following defines the stress at which a material begins to undergo plastic deformation?</p>	t	100	0	2018-05-20 13:59:21	2018-05-20 13:59:21	3	df28c48ca52be748f2817648bdd9ebe2
544	275	multiple-choice	<p>Which of the following is true of both calcium phosphate and calcium sulfate?</p>	t	100	0	2018-05-20 14:01:49	2018-05-20 14:01:49	3	bdab3104b4b397c844a1e1dbb4cddbac
545	276	multiple-choice	<p>Which of the following best describes the mechanism by which osteoprotegerin (OPG) plays a role in RANKLmediated osteoclast bone resorption?</p>	t	100	0	2018-05-20 14:04:41	2018-05-20 14:04:41	3	6c6a9f8eb56ba63c1fd4cafa5de68906
993	521	essay	<p>What is the most possible diagnosis? (25)</p>	f	100	0	2018-12-17 02:41:51	2018-12-19 08:12:30	3	f8b606b8bbe8efd71c69260b7ba2e65c
546	277	multiple-choice	<p>How does a dynamic compression plate achieve compression at the fracture of a long bone?</p>	f	100	0	2018-05-20 14:11:27	2018-05-20 14:13:26	3	69aa8b4576b9b335a77b3ca732f528a3
547	278	multiple-choice	<p>A 10-year-old child falls from a standing height and sustains the injury shown in Figure A. Her medical history includes hearing defects and the facial appearance shown in Figure B. In addition to operative fixation of her fracture she is scheduled to receive cyclical intravenous pamidronate administration as a treatment after the fracture is healed. Which of the following is associated with this form of treatment?</p>	t	100	0	2018-05-20 14:15:28	2018-05-20 14:15:28	3	40eebbc6abce2edeab848a5220f3457d
548	279	multiple-choice	<p>Laboratory values of a normal serum calcium and parathyroid hormone can be found in which of the following disease states?</p>	t	100	0	2018-05-20 14:18:28	2018-05-20 14:18:28	3	eca245ebe72dd33fc0cc2def72ef24c6
550	280	multiple-choice	<p>Which of the following conditions exhibit the inheritance pattern shown in Figure A, assuming no new mutations?</p>	t	100	0	2018-05-20 14:22:22	2018-05-20 14:22:22	3	6fda846b875377e3a8084c998a38296c
551	281	multiple-choice	<p>Low toughness is a disadvantage of which of the following bearing surfaces used in total hip arthroplasty?</p>	t	100	0	2018-05-20 14:24:38	2018-05-20 14:24:38	3	11017c53d0bd741604131b01b81b5cb5
552	282	multiple-choice	<p>Osteonecrosis of the jaw has been recognized as a possible complication of chronic therapy with which of the following medications?</p>	t	100	0	2018-05-20 14:26:30	2018-05-20 14:26:30	3	6f308002f68cd3e33c5daa929d3a58ae
600	330	multiple-choice	<p>Which of the following statements is true regarding polymethylmethacrylate (PMMA)?</p>	t	100	0	2018-05-20 23:34:42	2018-05-20 23:34:42	3	985cad7bd18c9d5b2e3650df4fa89e48
553	283	multiple-choice	<p>A 47-year-old man complains of long standing pain involving the right index, middle, and ring fingers. A clinical image is shown in Figure A. A radiograph is provided in Figure B. Which of the following is the most likely diagnosis?</p>	t	100	0	2018-05-20 14:31:27	2018-05-20 14:31:27	3	fb0eaa3db39898705463e684176e0809
554	284	multiple-choice	<p>Disruption of which of the following interrupts the major source of nutrients to the growth plate?</p>	t	100	0	2018-05-20 14:33:39	2018-05-20 14:33:39	3	b14b1982e83b6ccc09ea962dd0cb4fea
555	285	multiple-choice	<p>Radiographic changes suggestive of osteopetrosis in children are a known complication of which of the following types of medications?</p>	t	100	0	2018-05-20 14:35:35	2018-05-20 14:35:35	3	ca60635150bff34feae01cf929de0a00
556	286	multiple-choice	<p>The 2009 AAOS Clinical Guideline on prevention of pulmonary embolism in patients undergoing total hip or knee arthroplasty recommends classifying patients as having either a &quot;standard&quot; or &quot;elevated&quot; risk of bleeding complications. The presence of all of the following qualify a patient as having an &quot;elevated&quot; risk of major bleeding<strong> EXCEPT</strong>?</p>	t	100	0	2018-05-20 14:37:50	2018-05-20 14:37:50	3	b725ae29f44692ed65618a91d9985912
557	287	multiple-choice	<p>Which of the following is NOT a component of Virchow&#39;s triad?</p>	t	100	0	2018-05-20 14:39:46	2018-05-20 14:39:46	3	46557dd88a8dd7daebdb49faa2246807
558	288	multiple-choice	<p>A 64-year-old woman with a longstanding history of rheumatoid arthritis complains of finger dysfunction for the past 6 months. Figure A displays her hand during active extension of all fingers. Figure B displays her hand maintaining her fingers extended following passive extension. What is the next most appropriate treatment for the ring finger?</p>	t	100	0	2018-05-20 14:43:14	2018-05-20 14:43:14	3	30fd9e5a7b00d85a02fac766ead52ae1
559	289	multiple-choice	<p>A study was designed to measure the benefit of subacromial corticosteroid injections. Participants were randomized to methylprednisolone acetate 40 mg with lidocaine 1% or lidocaine 1% alone. The participants were not provided with information of their treatment allocation. The subacromial injection was prepared and administered by a single orthopaedic surgeon. Results were collected by the orthopaedic surgeon using clinical and patient satisfaction outcome scores at 6, 12, and 24 weeks. Which of the following would best describe this type of study?</p>	t	100	0	2018-05-20 14:45:26	2018-05-20 14:45:26	3	863b06d003c2ffb1654c2adda1163e51
560	290	multiple-choice	<p>A 53-year-old male laborer presents to his primary care physician with complaints of acute onset of left knee pain. He has had mild episodes of knee pain in the past and is two years status post a left partial medial meniscectomy. He has had mild relief with the use of anti-inflammatories. His past medical history is significant only for hyperparathyroidism and mild hypertension. He denies any fevers or chills. His exam reveals a moderate knee effusion and diffuse pain and tenderness with palpation and range of motion. Weightbearing radiographs are shown below. The most likely etiology of the patient&#39;s knee pain is characterized by which finding?</p>	t	100	0	2018-05-20 14:48:14	2018-05-20 14:48:14	3	d64ee2705e76beeed390018aa04a705d
561	291	multiple-choice	<p>A surgeon chooses a periarticular locking plate with unicortical proximal locking screws for an extra-articular distal femur fracture as seen in Figure A. Compared to a fixed-angle blade plate construct with bicortical unlocked proximal screw fixation, the periarticular locking plate with unicortical locking screws has which biomechanical properties?</p>	t	100	0	2018-05-20 14:51:18	2018-05-20 14:51:18	3	afb9fc0b38d336fac693f9f5289401b3
562	292	multiple-choice	<p>Which of the following is NOT included in the best management of a elderly female newly diagnosed with a fragility fracture?</p>	t	100	0	2018-05-20 14:53:33	2018-05-20 14:53:33	3	802f4968fed7f8cb29fb374f7d6189e9
563	293	multiple-choice	<p>You are planning an intramedullary nail to treat a geriatric patient with a peritrochanteric femur fracture. Which of the following preoperative considerations is correct regarding your implant?</p>	t	100	0	2018-05-20 14:56:00	2018-05-20 14:56:00	3	9a7c8aa874cc944d5859effd1d86856d
564	294	multiple-choice	<p>Figure A is a radiograph taken after an open reduction and internal fixation of a periprosthetic distal femur fracture. With this type of hybrid locked plate fixation, what is the difference between screw A and screw B</p>	t	100	0	2018-05-20 14:59:06	2018-05-20 14:59:06	3	2f385a9e6f38fda42e6e7abde530971f
565	295	multiple-choice	<p>All of the following are AAOS recommendations regarding prevention of venous thromboembolic disease (VTED) in patients undergoing elective hip and knee arthroplasty EXCEPT?</p>	t	100	0	2018-05-20 15:12:44	2018-05-20 15:12:44	3	a90dd673d20a3cd2f9ed77185a58e56c
566	296	multiple-choice	<p>A 7-year-old recent immigrant presents with pain and tenderness over the legs. Physical exam shows the gums have a bluish-purple hue with areas of hemorrhages. A radiograph is shown in Figure A. In Figure B, what region of the growth plate is most affected in this condition?</p>	t	100	0	2018-05-20 15:16:09	2018-05-20 15:16:09	3	958dfa8914f6ca5626f8e0443f59b05b
567	297	multiple-choice	<p>What is the cellular mechanism of action for non-nitrogen containing bisphosphonates (such as clodronate and etidronate) to induce osteoclast apoptosis?</p>	t	100	0	2018-05-20 15:17:59	2018-05-20 15:17:59	3	fb5141b02bc7890597de058cdc57b85c
568	298	multiple-choice	<p>A 65-year-old female undergoes a total knee arthroplasty. In addition to chemoprophylaxis for deep vein thrombosis (DVT) prevention she is given pneumatic compression devices. Which of the following is associated with pneumatic compression devices?</p>	t	100	0	2018-05-20 15:19:54	2018-05-20 15:19:54	3	e2c70552a02aa4801681ddf7fc322cfd
569	299	multiple-choice	<p>The greatest biomechanical difference between unicortical and bicortical locking screws is seen when what force is applied?</p>	t	100	0	2018-05-20 15:22:16	2018-05-20 15:24:42	3	05d99dc82f9dba7f707aafd0790629b7
570	300	multiple-choice	<p>Iliac crest cancellous bone graft can be harvested from either the anterior or posterior aspect of the pelvis. When comparing these two locations, harvesting from the anterior iliac crest has which of the following?</p>	t	100	0	2018-05-20 15:28:03	2018-05-20 15:28:03	3	b40638046b0a3787aae85fdd495d6169
664	369	multiple-choice	<p>What might be the route of infection in this case?</p>	f	100	0	2018-07-06 03:36:36	2018-07-06 03:36:36	3	9536fb6d4608eb744a6c0d1a832af049
571	301	multiple-choice	<p>A 52-year old woman who is not on any hormone replacement therapy (HRT) falls from standing height and sustains the injury seen in Figure A. Review of her medical history reveals that she carries a diagnosis of osteoporosis, and that her latest T-score was -3.0. How much calcium should she have been consuming on a daily basis prior to sustaining her injury?</p>	f	100	0	2018-05-20 15:30:21	2018-05-20 15:32:50	3	5a807912d96f9a6ac2ed4a877d5431bc
572	302	multiple-choice	<p>Which of the following patients are at greatest risk of having a future vertebral fragility fracture?</p>	t	100	0	2018-05-20 15:32:36	2018-05-20 15:32:36	3	43c1d43933383201cf9ce59b16250ade
686	375	essay	<div>List 2 approach options for fixation of this fracture !</div>	f	100	0	2018-07-13 10:21:08	2018-07-13 10:21:08	3	8e673de19a46b418f1fc64be43f9b9cd
573	303	multiple-choice	<p>You are seeing a dialysis patient for a fragility fracture. This patient also carries a diagnosis of renal osteodystrophy. What is the key pathophysiological step that is responsible for his osteomalacia?</p>	t	100	0	2018-05-20 15:35:39	2018-05-20 15:35:39	3	75556f53a8c81e9ade2e83219ab92d88
574	304	multiple-choice	<p>A 25-year-old male sustains an transverse humeral shaft fracture and undergoes open reduction and internal fixation with rigid compression plating. What kind of bone healing would be expected with this type of fracture fixation?</p>	t	100	0	2018-05-20 15:51:00	2018-05-20 15:51:00	3	e79739739db5788cd9f0bb5b6fe7af95
575	305	multiple-choice	<p>With aging, there is a greater loss of mechanical strength in which of the following types of bone?</p>	t	100	0	2018-05-20 22:23:39	2018-05-20 22:23:39	3	be0418b045b38d9b97c0458128e3fa32
576	306	multiple-choice	<p>Locking plate technology has relative indications for use in all of the following, <strong>EXCEPT</strong></p>	t	100	0	2018-05-20 22:25:44	2018-05-20 22:25:44	3	7ac691af7dca230632ce9aaf1a368282
577	307	multiple-choice	<p>Which of the following techniques increases strength and stability to an external fixation construct?</p>	t	100	0	2018-05-20 22:29:16	2018-05-20 22:29:16	3	ef6885d3a2de3b9a027d5028b681f1b9
578	308	multiple-choice	<p>A 55-year-old healthy female presents for a routine physical exam. In regards to bone health and osteoporosis prevention, what dose of calcium and vitamin D should be recommended for daily consumption?</p>	t	100	0	2018-05-20 22:31:27	2018-05-20 22:31:27	3	a94235c71bf45d24ee68bd21f3e1c4d0
579	309	multiple-choice	<p>A 79-year-old female with a history of congestive heart failure falls onto her right hip at home. A radiograph is provided in Figure A. She undergoes an uncemented unipolar hemiarthroplasty. During insertion of the stem into the femoral canal, the patient becomes hypotensive and hypoxic. Which of the following has most likely occurred?</p>	t	100	0	2018-05-20 22:34:09	2018-05-20 22:34:09	3	474db11a31d1de12d045332a54ececca
580	310	multiple-choice	<p>All of the following medications have been associated with an increased risk of osteoporosis <strong>EXCEPT</strong></p>	t	100	0	2018-05-20 22:36:20	2018-05-20 22:36:20	3	c02b8c39dee8f78548bdba4bbddd9e4b
581	311	multiple-choice	<p>The nonunion as seen in Figure A will most likely unite by what intervention?</p>	t	100	0	2018-05-20 22:38:35	2018-05-20 22:38:35	3	a17923da7b31b77654b36896cae8d3e0
582	312	multiple-choice	<p>A 60-year-old man has had intermittent pain in his right great toe for the past 2 years. What is the most likely cause for the lesions shown in Figure A?</p>	t	100	0	2018-05-20 22:40:47	2018-05-20 22:40:47	3	20e1e4f883c2fff9e1de4044eea96fd1
583	313	multiple-choice	<p>A patient with chronic renal disease would expect which of the following endocrine abnormalities?</p>	t	100	0	2018-05-20 22:44:22	2018-05-20 22:44:22	3	26666c681033eb901c5940f6da00cd5d
584	314	multiple-choice	<p>An orthopaedic resident wants to answer a focused research question of whether mobile bearing knee arthroplasty has superior functional outcomes compared to fixed bearing knee arthroplasty. The resident mathematically combines the results from multiple retrospective cohort studies following QUORUM (Quality of Reporting of Meta-analyses) guidelines. What is the highest level of evidence that this meta-analysis can achieve?</p>	t	100	0	2018-05-20 22:48:24	2018-05-20 22:48:24	3	60b3ffa31d8f18ed00715017eb95c961
585	315	multiple-choice	<p>Which of the following is true regarding rigid locking plate constructs in fracture fixation?</p>	t	100	0	2018-05-20 22:50:16	2018-05-20 22:50:16	3	b524a0d7c03786dffcef27a9a8aa1a4b
586	316	multiple-choice	<p>A 24-year-old female presents with a transverse midshaft humerus fracture. Which of the following implants would create the most compression on both the far and near cortices?</p>	f	100	0	2018-05-20 22:52:49	2018-05-20 22:52:59	3	7031a3bb5ec25a2dd290fa8d3c657ea9
587	317	multiple-choice	<p>Mesenchymal stem cells have the capacity to differentiate into all the following cell types EXCEPT?</p>	t	100	0	2018-05-20 22:56:37	2018-05-20 22:56:37	3	138ac4a132a3ebe559d7d3a9c7caedab
588	318	multiple-choice	<p>Which of the following most accurately describes stainless steel?</p>	t	100	0	2018-05-20 22:58:35	2018-05-20 22:58:35	3	324a0279d1209d9234ba3d9680117273
589	319	multiple-choice	<p>Calcitonin plays a role in bone metabolism by which of the following mechanisms?</p>	t	100	0	2018-05-20 23:00:32	2018-05-20 23:00:32	3	33f514ac11607b29236cc0431d20573d
590	320	multiple-choice	<p>All of the following are clinical features of complex regional pain syndrome (reflex sympathetic dystrophy) of the lower extremity <strong>EXCEPT:</strong></p>	t	100	0	2018-05-20 23:02:47	2018-05-20 23:02:47	3	4a51605b7ce4eb3764f9d71d3d6bf823
591	321	multiple-choice	<p>Salter-Harris type I fractures typically occur through which zone of the physis?</p>	t	100	0	2018-05-20 23:06:18	2018-05-20 23:06:18	3	18c82fcd58dd4399c006986119cf71e6
592	322	multiple-choice	<p>Regarding bone densitometry, a T-score of -3.5 is defined as which of the following?</p>	t	100	0	2018-05-20 23:08:43	2018-05-20 23:08:43	3	a853e03e986f357957c11916dd0aceb8
593	323	multiple-choice	<p>Which of the following defines the working distance of a plate in a plate/screw fracture fixation construct?</p>	t	100	0	2018-05-20 23:12:02	2018-05-20 23:12:02	3	982c9ca52e6f296639b60b21179dc90d
594	324	multiple-choice	<p>Ligaments attach to bone by both direct insertion and indirect insertion. Which of the following most accurately describes the order of the four transition zones of direct insertion?</p>	t	100	0	2018-05-20 23:13:57	2018-05-20 23:13:57	3	5230b2bb77874245b19bf70de4f346e4
595	325	multiple-choice	<p>Which of the following sarcomas is correctly paired with its most common translocation?</p>	f	100	0	2018-05-20 23:18:43	2018-05-20 23:20:07	3	e583a422f80033e941a2a25830ca0712
596	326	multiple-choice	<p>A therapeutic study presents a systematic review of 15 high-quality randomized controlled trials with homogeneous results. What level of evidence is this considered?</p>	t	100	0	2018-05-20 23:21:22	2018-05-20 23:21:22	3	e7196b05e5f4d816f98f223a5ddfa0aa
597	327	multiple-choice	<p>After application of a unilateral tibial external fixator, it is observed that the frame does not provide sufficient rigidity across the fracture site. Altering the external fixator in which of the following ways will have the greatest impact on frame stiffness?</p>	t	100	0	2018-05-20 23:24:33	2018-05-20 23:24:33	3	820c85dcfc6ed5f6ffd24ad7d007f84c
598	328	multiple-choice	<p>A busy orthopaedic surgeon enters the operating suite to a prepped and draped patient who is scheduled for a right knee ACL reconstruction. During the diagnostic arthroscopy, the surgeon sees an intact ACL. The MRI is reviewed and found to be of the left knee. Wrong site surgery could have been likely avoided if which of following was done?</p>	t	100	0	2018-05-20 23:28:36	2018-05-20 23:28:36	3	8d9a1a1a4f759e9741a82df1dd9b680d
599	329	multiple-choice	<p>Which of the following biochemical changes are common to both aging cartilage and osteoarthritis (OA) cartilage?</p>	t	100	0	2018-05-20 23:31:08	2018-05-20 23:31:08	3	113b18ea10f453a1531d963a97c2494d
687	375	essay	<div>Mention 3 potential complications that may occur?</div>	f	100	0	2018-07-13 10:21:08	2018-07-13 10:21:08	3	95b77a3f0c05dcd3fba25177659e5b46
601	331	multiple-choice	<p>In a patient undergoing total knee arthroplasty, the femoral and tibial bone resections can be done using intra-or extra-medullary alignment systems. Extra-medullary guidance systems have what benefit over intra-medullary guidance systems</p>	t	100	0	2018-05-20 23:38:13	2018-05-20 23:38:13	3	ddda4210d87244ad5fadc019c9042377
602	332	multiple-choice	<p>A 52-year-old male underwent a right total knee arthroplasty 3 days ago and reports new onset dyspnea. His vitals signs include a temperature of 98.8, pulse of 133, blood pressure of 130/77, respiratory rate of 28, and oxygen saturation of 91% on room air. A chest radiograph shows atelectasis. Which of the following findings is most likely also present?</p>	t	100	0	2018-05-20 23:49:38	2018-05-20 23:49:38	3	659e4262959bc8a0a68cb58ecf8b6dd7
603	333	multiple-choice	<p>Receptor activator of nuclear-factor kappa-B ligand (RANKL) is an important regulator of bone resorption. Which of the following cells is the MAJOR source of RANKL in bone remodelling?</p>	t	100	0	2018-05-20 23:51:45	2018-05-20 23:51:45	3	0ec7e8e654cfacfd78401be01bb57655
604	334	multiple-choice	<p>Which of the following laboratory values would be consistent with nutritional rickets?</p>	t	100	0	2018-05-20 23:53:17	2019-11-08 09:41:30	3	2e89566a9dc39d1dfa2aa8d668f7cda2
605	335	multiple-choice	<p>Which of the following substances is osteoinductive?</p>	t	100	0	2018-05-20 23:55:51	2018-05-20 23:55:51	3	9fba49186bff8a429258612746f15824
606	336	multiple-choice	<p>The resistance to pull out of a screw in osteoporotic bone is increased by all of the following <strong>EXCEPT?</strong></p>	t	100	0	2018-05-20 23:58:40	2018-05-20 23:58:40	3	a52a4035ea87c419a67f30d480940cca
607	337	multiple-choice	<p>What laboratory findings would you expect to find in a patient newly diagnosed with renal osteodystrophy?</p>	t	100	0	2018-05-21 00:00:31	2018-05-21 00:00:31	3	27d8ce0b6eb41d731a26788e3904d456
608	338	multiple-choice	<p>The elements chromium, molybdenum, and cobalt are basic components of which of the following implant materials?</p>	t	100	0	2018-05-21 00:02:11	2018-05-21 00:02:11	3	73b2851f1227ee7a0c899a6167288981
609	339	multiple-choice	<p>Which of the following is most often implicated as an etiology for a hypertrophic nonunion?</p>	t	100	0	2018-05-21 00:04:12	2018-05-21 00:04:12	3	e98dd928ffede0f4d316a529522ae4ae
610	340	multiple-choice	<p>An 85-year-old woman undergoes the treatment seen in Figure A for a displaced left femoral neck fracture. During wound closure, the patient becomes hypoxic and hypotensive. Despite aggressive resuscitation efforts, she passes away three hours later in the intensive care unit. The autopsy findings seen in Figure B from the patient&#39;s lungs are most likely the result of which of the following</p>	t	100	0	2018-05-21 00:07:08	2018-05-21 00:07:08	3	4ddce780e4317d395a0d057d92d32de9
611	341	multiple-choice	<p>Limited contact dynamic compression (LCDC) plates have what advantage over standard dynamic compression plates?</p>	t	100	0	2018-05-21 00:08:49	2018-05-21 00:08:49	3	811801b9b9a52090e750d43eeb42e403
612	342	multiple-choice	<p>An adolescent patient is treated with a 6mm solid intramedullary nail. Compared to a 12mm solid nail of the same material, the 6mm nail has</p>	t	100	0	2018-05-21 00:10:26	2018-05-21 00:10:26	3	f62bb1906829a46388ad215abc96f5da
613	343	multiple-choice	<p>A 34-year-old female undergoes open reduction and internal fixation (ORIF) for the left lower extremity injury shown in Figures A-C. Her postoperative weight bearing protocol includes touch down weight bearing to the left lower extremity. Which of the following ambulatory support devices is most appropriate for this patient?</p>	t	100	0	2018-05-21 00:15:08	2018-05-21 00:15:08	3	0d488e347aa6805d0d6e5d8159dea61d
614	344	multiple-choice	<p>Patients display a Trendelenburg gait to compensate for weakness in which of the following muscle groups?</p>	t	100	0	2018-05-21 00:17:18	2018-05-21 00:17:18	3	c19cab696f9cb0f0dc9f10c0c193b4f1
615	345	multiple-choice	<p>A 27-year-old male undergoes intramedullary nailing of a midshaft tibia fracture with static locking proximally and distally. There is minimal healing noted 3 months postoperatively and the decision is made to dynamize the nail. For intramedullary nail dynamization, an interlocking screw should be placed in which of the holes shown in Figure A?</p>	t	100	0	2018-05-21 00:21:31	2018-05-21 00:21:31	3	8868a78e2a6f60aeddb6dc83b9cf8e94
616	346	multiple-choice	<p>A locked plate used in a bridge plate fashion is biomechanically most similar to which of the following fixation methods?</p>	t	100	0	2018-05-21 00:23:26	2018-05-21 00:23:26	3	9a4735430d065157a209d72fee85efaf
617	347	multiple-choice	<p>Long-term bisphosphonate usage has been shown to cause an increased risk of stress reaction leading to fracture at which of the following areas?</p>	t	100	0	2018-05-21 00:25:23	2018-05-21 00:25:23	3	6ec0ebc2a9803f0235845195c3ebaf3e
618	348	multiple-choice	<p>The posterior wall of the acetabulum is best visualized on which of the following radiographic views?</p>	t	100	0	2018-07-01 10:02:03	2018-07-16 18:51:18	3	14d34b41a5b74ac19ff2750b2d1fb507
619	349	multiple-choice	<p>During the ilioinguinal approach to the pelvis, the corona mortis artery must be identified and ligated if present. The corona mortis artery joins the external illiac artery with which other major artery?</p>	f	100	0	2018-07-01 10:06:25	2018-07-01 10:06:25	3	068cc3d66d15cd83770d8d92740f3e62
620	350	multiple-choice	<p>Which of the following is NOT a contraindication to nonoperative treatment with a coaptation splint?</p>	f	100	0	2018-07-01 10:09:49	2018-07-04 22:44:19	3	8f9ee680980e741a7b0d559cf239fb58
621	350	multiple-choice	<p>Which of the following about the radial nerve is true?</p>	t	100	0	2018-07-04 22:44:19	2018-07-04 22:44:19	3	7f70f6b9762b3ccbb38055232625607f
622	350	multiple-choice	<p>The same patient is treated with an intramedullary nail and is later lost to follow-up. He presents to clinic 9 months later with a complaint of persistent right arm pain. Radiographs and advanced imaging reveal that he has a hypertrophic nonunion.</p>\n\n<p>What is the most appropriate definitive treatment?</p>	t	100	0	2018-07-04 22:44:19	2018-07-04 22:44:19	3	1d75eb2e812b703ddeef0e85ddfc9e9c
623	351	multiple-choice	<p>A 34-year-old, male bull rider is brought to the emergency department after being bucked from his bull. He has abrasions over his left upper and lower extremities with exquisite tenderness over his left hip, pain with longroll, and tenderness in his left groin. An AP pelvis radiograph is shown in Figure.</p>\n\n<p><strong>What treatment is most appropriate in this injury?</strong></p>	t	100	0	2018-07-04 22:51:33	2018-07-04 22:51:33	3	5d1e167a83dab88ad809fd190635ef57
624	352	multiple-choice	<p>What is the advantage of medial and lateral crossed pins compared to two lateral pins in the treatment of supracondylar humerus fractures?</p>	t	100	0	2018-07-04 22:54:05	2018-07-04 22:54:05	3	c07aa7695c5b9ad11dccb8357579759f
625	353	multiple-choice	<p>What is the most appropriate next step?</p>	t	100	0	2018-07-04 22:59:10	2018-07-04 22:59:10	3	c01c00e739c4ff0aa7d2ea15e6bc11d0
626	353	multiple-choice	<p>What other diagnostic study should be obtained?</p>	t	100	0	2018-07-04 22:59:10	2018-07-04 22:59:10	3	1d711901dbffb80a770f96993740a666
688	376	essay	<div>List all Hand compartments! (50)</div>	f	100	0	2018-07-14 09:22:03	2018-07-14 21:22:46	3	ed8243f8c30b1670107a7b1efab3e0c4
627	353	multiple-choice	<p>What is the most important predictor that he may benefit from surgical fixation of his fracture?</p>	t	100	0	2018-07-04 22:59:10	2018-07-04 22:59:10	3	4b48de0d1f10f110dfe4fd0b269c3ef7
628	354	multiple-choice	<p>A 36-year-old man was injured in a motorcycle collision and sustained the injury shown in Figure below. He has a blood pressure (BP) of 70/40 mm Hg, pulse of 148 bpm is negligible urine output. His airway is secure and intravenous (IV) access is obtained. Two liters of warm crystalloid solution are given; repeated vital signs reveal the same BP and a pulse of 142 bpm.</p>\n\n<p><strong>What is the best next step?</strong></p>	t	100	0	2018-07-04 23:02:09	2018-07-04 23:02:09	3	70dfb61651798b0156de10c6aa0435ea
921	485	multiple-choice	<p>An 85-years-old woman who fell from a standing height 1 week earlier. She is independent and ambulatory and resides in an assisted living facility. She reports persistent neck pain but denies arm pain or weakness. She is neurologically intact. Fracture in this region of C2 have a high risk of..?</p>	t	100	0	2018-12-04 18:54:48	2018-12-04 18:54:48	3	999ca0bad2c1b0139c5107e2496c0915
629	355	multiple-choice	<p>A 17-year-old football player is tackled with an opposing player&#39;s helmet hitting him hard in the abdomen. He is knocked backwards and suffers a diaphyseal femur fracture. He denies any loss of consciousness. Vital signs reveal a heart rate of 118, mean arterial pressure (MAP) of 68, and a respiration rate of 32 per minute. A FAST ultrasound study shows trace free fluid in the perisplenic space. A CBC taken prior to bolus IV fluids reveals a hematocrit of 48%, and a blood gas shows a lactate level of 1.8 and a base excess of -2.0.<br />\n<strong>Which of the follow statements regarding the patient&#39;s hemodynamic status is correct?</strong></p>	t	100	0	2018-07-05 06:22:05	2018-07-05 06:22:16	3	ba7a406d2cf227aa7a9310c4e388cf20
630	356	multiple-choice	<p>A 27-year-old female sustains injuries to the left femur and ipsilateral tibia shown in Figures below following an ATV accident.&nbsp; Her injury severity score (ISS) is 27 for her musculoskeletal and abdominal injuries.&nbsp; Her left limb is neurovascularly intact and there are no signs of compartment syndrome.</p>\n\n<p><strong>What is the most appropriate definitive management?</strong></p>	t	100	0	2018-07-05 11:10:58	2018-07-05 11:10:58	3	429d3500ec9eca5a385ba6ac487fcd04
631	357	multiple-choice	<p>A 31-year-old male driver was involved in a high-speed motor vehicle accident. His injuries include a left subdural hematoma (Abbreviated Injury Score [AIS]=4), left segmental femur fracture (AIS=3), ruptured spleen (AIS=4), nasal fracture (AIS=2), fractured left ribs 4 to 7 (AIS=2), and a closed pelvic ring fracture (AIS=3).</p>\n\n<p><strong>What is his Injury Severity Score (ISS)?</strong></p>	t	100	0	2018-07-05 11:13:15	2018-07-05 11:13:15	3	8c768b216890cb1708dc10ec65f1ff73
632	358	multiple-choice	<p>The most likely diagnosis is</p>	t	100	0	2018-07-05 11:18:29	2018-07-05 11:18:29	3	038db4eb49415269a0c619687fe8ede2
633	358	multiple-choice	<p>Treatment at this time should be</p>	t	100	0	2018-07-05 11:18:29	2018-07-05 11:18:29	3	53fd142f1e4332ff2812c2427d5a2e49
634	358	multiple-choice	<p>The above patient is undergoing a vertebroplasty of the fractured level. During the procedure, you note that cement has extravasated posterior to the vertebral body.</p>\n\n<p><strong>The next most appropriate step in management is?</strong></p>	t	100	0	2018-07-05 11:18:29	2018-07-05 11:18:29	3	e575cdb7d0cb22977a3b8765f269b369
635	359	multiple-choice	<p>What is the most likely diagnosis?</p>	t	100	0	2018-07-05 11:24:16	2018-07-05 11:24:16	3	d804b406825e480dafcf6c8ac1f8ac9f
636	359	multiple-choice	<p>In the general population, what is the most commonly torn rotator cuff muscle?</p>	t	100	0	2018-07-05 11:24:16	2018-07-05 11:24:16	3	7eaa3f811a65f1f5aa2cbea6fa4f7d65
637	359	multiple-choice	<p>What physical examination maneuver best tests for a supraspinatus tear?</p>	t	100	0	2018-07-05 11:24:16	2018-07-05 11:24:16	3	0c640d35e4577127ff6c9169444ff60e
638	360	multiple-choice	<p>An x-ray taken and shown above. What does he have?</p>	t	100	0	2018-07-05 11:30:23	2018-07-05 11:30:23	3	36420e667e0cce430fe1cda1bb0f4894
639	360	multiple-choice	<p>You choose to perform a scaphoid excision with four-corner fusion. Which four bones are fused in this procedure?</p>	t	100	0	2018-07-05 11:30:23	2018-07-05 11:30:23	3	2831060c46f38fae2285f47a5f58af59
640	361	multiple-choice	<p>All of the following are modifiable risk factors for knee osteoarthritis<strong>, except:</strong></p>	t	100	0	2018-07-05 12:21:09	2018-07-18 03:42:43	3	efebd753678f5059aefdc55b2f060a07
641	361	multiple-choice	<p>All the following are viable initial management options recommended by the AAOS, except</p>	t	100	0	2018-07-05 12:21:09	2018-07-18 03:42:43	3	d504f5f3977a53f587e570d67f8b31fa
642	362	multiple-choice	<p>What is the correct diagnosis?</p>	t	100	0	2018-07-05 12:44:09	2018-07-05 12:44:09	3	c4a9e2d8cabf691cada7b8a973703314
643	362	multiple-choice	<p>The patient has a lytic lesion in the left proximal femur which is shown above(GAMBAR 2). He has pain with weight bearing and resultant ambulatory dysfunction. What would you recommend at this time regarding this finding?</p>	t	100	0	2018-07-05 12:44:09	2018-07-05 12:44:09	3	ebaf003073abee2571c920cdf46bc91f
644	363	multiple-choice	<p>On examination, the patient is cooperative, but not able to move her fingers in all the ways that you ask her to do. The most likely nerve injury with this fracture is:</p>	t	100	0	2018-07-05 12:47:28	2018-07-05 12:47:28	3	81205f9eee6d1141c62a5e95ca33a3e3
645	363	multiple-choice	<p>The patient is taken to the operating room and a reduction is successfully obtained via closed means. Three lateral pins are placed to hold the reduction. Following surgery, the nerve is still not functioning. The most appropriate next step would be:</p>	t	100	0	2018-07-05 12:47:28	2018-07-05 12:47:28	3	24aeff6c631893b6ab4c6923a4d54590
646	364	multiple-choice	<p>What is the best next step?</p>	t	100	0	2018-07-06 01:26:26	2018-07-06 01:26:26	3	58beb0a20979d1ee7cc138fe41ec4b66
647	364	multiple-choice	<p>What is the most likely outcome following treatment for this condition?</p>	t	100	0	2018-07-06 01:26:26	2018-07-06 01:26:26	3	3605cf87fd8428490d6e73aeacbad94b
648	364	multiple-choice	<p>Several days following treatment, weakness of grade 2 of 5 develops in the right deltoid and biceps.</p>\n\n<p><strong>What complication most likely caused this change?</strong></p>	t	100	0	2018-07-06 01:26:26	2018-07-06 01:26:26	3	cb0e9ef8dfe14c9ae464c782a43e39ca
649	364	multiple-choice	<p>What is the most appropriate treatment for this complication?</p>	t	100	0	2018-07-06 01:26:26	2018-07-06 01:26:26	3	c955a8041a96863fb471bb46c6f5067a
650	365	multiple-choice	<p>A 55-year-old woman with rheumatoid arthritis reports that she awoke with an inability to flex the interphalangeal joint of her thumb. Figure shows an intraoperative finding.</p>\n\n<p><strong>What is the most appropriate surgical treatment?</strong></p>	t	100	0	2018-07-06 01:30:25	2018-07-06 01:30:25	3	81c8e8d518d39f7b046e6f3ada11f3b8
651	366	multiple-choice	<p>Based on the physical finding during the primary survey which of the following best describe the patient?</p>	f	100	0	2018-07-06 02:53:50	2018-07-06 03:06:06	3	f3fef8a3bd44c9c2ca5822f452f45ed1
652	366	multiple-choice	<p>Related with the biomechanics of the displacement, which of the following is associated with the direction of displacement of femur fracture?</p>	t	100	0	2018-07-06 03:06:06	2018-07-06 03:06:06	3	7eba0de5a9c17ae94ffd9f1e7bc129b7
689	376	essay	<div>Describe how to perform fasciotomy of hands! What compartment released each incision ? (50)</div>	f	100	0	2018-07-14 09:22:03	2018-07-14 21:22:46	3	5dfd25b5ad9fe53d0b3c87a210262f42
653	366	multiple-choice	<p>16 hours after admission the patient has decreased of consciousness, with unresponsive, seizure and respiratory distress. From the history taking you notice that head injury was not found, however, the Head CT following seizure showed pathognomonic white-matter punctate lesions and watershed involvement. The possible explanation for his findings are?</p>	t	100	0	2018-07-06 03:06:06	2018-07-06 03:06:06	3	b4cc64de4c8d29213156d8bc873c1987
654	366	multiple-choice	<p>After stabilized, the patient is planned to perform internal fixation for the femur. Related with its possibility of creating iatrogenic fat embolism, which of the following order of surgery create the most possible FES?</p>	f	100	0	2018-07-06 03:06:06	2018-07-06 03:06:06	3	115a9de38f96e354b87f2bdd7d079120
655	367	multiple-choice	<p>Six hours after the accident the ward nurse calls you and said that the patient develops intense excruciating and progressive pain despite the successful provisional immobilization of the splint. What is the most possible early sign that you might found to diagnose compartment syndrome in this patient?</p>	f	100	0	2018-07-06 03:17:09	2018-07-06 03:17:09	3	e0fbef1d24959afd24abc8edf09d365a
656	367	multiple-choice	<p>Recognizing the Delta P is related with the increase pressure in compartment syndrome. The correct description of Delta P is?</p>	f	100	0	2018-07-06 03:17:09	2018-07-06 03:17:09	3	24ee21b859800232129f6d9d70f207d1
657	367	multiple-choice	<p>After the objective evaluation, you diagnose this patient as compartment syndrome. What is the first action will you perform to this patient in the ward?</p>	t	100	0	2018-07-06 03:17:09	2018-07-06 03:17:09	3	ba56bd075ab2ebb03012c86a55bd191f
658	367	multiple-choice	<p>Due to the progressing condition, you finally decide to perform dual (medial-lateral) incision fasciotomy. The following best corresponds with your planned decision</p>	t	100	0	2018-07-06 03:17:09	2018-07-06 03:17:09	3	770fea09adbcf3a15a6a1a8fc311ad7c
659	368	multiple-choice	<p>What is the possible etiology of decreased neurologic finding in this patient?</p>	t	100	0	2018-07-06 03:27:04	2018-07-06 03:27:04	3	385b02219734a024bcfafe4ac6330bc7
660	368	multiple-choice	<p>The laboratory findings that support tuberculosis is accordingly with?</p>	f	100	0	2018-07-06 03:27:04	2018-07-06 03:27:04	3	529b830d6538849bc039ebbe7657b5be
661	368	multiple-choice	<p>Which of the following condition that best suggests this patient to perform surgical management?</p>	t	100	0	2018-07-06 03:27:04	2018-07-06 03:27:04	3	883ea8c36628ede32a9b2d0f49735cac
662	368	multiple-choice	<p>Which of the following procedure should be your target of surgical management for this patient?</p>	t	100	0	2018-07-06 03:27:04	2018-07-06 03:27:04	3	9b25714bffecb249700dae6322c2cf78
663	369	multiple-choice	<p>Based on the above description what is the possible diagnose for the patient?</p>	f	100	0	2018-07-06 03:36:36	2018-07-06 03:36:36	3	0cb6f899a636895789ed7e8d9c71a0e6
665	369	multiple-choice	<p>The posterior epidural extension of hyperintensity is suggestive of epidural abscess that encroaches the canal. Which of the following statement corresponds with epidural abscess?</p>	t	100	0	2018-07-06 03:36:36	2018-07-06 03:36:36	3	d2a081c1605c0d05ae95e062b8616d8b
666	369	multiple-choice	<p>The following will be best for this patient&rsquo;s management</p>	f	100	0	2018-07-06 03:36:37	2018-07-06 03:36:37	3	61d7774f821e699ab14fe1aa77c9db71
667	370	multiple-choice	<p>What is the most suitable diagnosis for this patient?</p>	t	100	0	2018-07-06 06:38:15	2018-07-06 06:38:15	3	9c6d95c4733fa41ee5c7ae316467aedf
668	370	multiple-choice	<p>Despite the unclear and multifactorial etiology, the following condition may be associated with the incidence of OPLL</p>	f	100	0	2018-07-06 06:38:15	2018-07-06 06:38:15	3	e69e4668df075c8a9f2f97f7344b7fc8
669	370	multiple-choice	<p>From the lateral x-ray of the cervical spine, you noticed that the patient also have loss of lordosis. Based on the above scenario and finding which surgical management will have better cervical fusion for this patient?</p>	t	100	0	2018-07-06 06:38:15	2018-07-06 06:38:15	3	4c89ba68f4909d9252d0a827ed5bd24b
670	370	multiple-choice	<p>One of the possible complications of anterior OPLL surgery is the dural tear. Which of the following technique can be applied to minimize this complication?</p>	f	100	0	2018-07-06 06:38:15	2018-07-06 06:38:15	3	df6e39efb7e8a9fc9072997381c3c7a3
671	371	multiple-choice	<p>Based on the information what advice will you give her?</p>	t	100	0	2018-07-06 06:48:57	2018-07-06 06:48:57	3	8c612c2f56fc8cae3e1fd1d58b1b48df
672	371	multiple-choice	<p>The patient asks you about the cause of why she has this ailment, which of the following best describe her condition?</p>	f	100	0	2018-07-06 06:48:58	2018-07-06 06:48:58	3	00a535a6caf4881346f41f567aba9135
673	371	multiple-choice	<p>She showed you the X-ray and point the osteophyte formation that is occurring in the medial part of her knee. What is the pathophysiology of the osteophyte that you can explain to her?</p>	t	100	0	2018-07-06 06:48:58	2018-07-06 06:48:58	3	aced618c911cbe47b27422ca8b7cccba
674	371	multiple-choice	<p>Related with the cardinal signs of osteoarthritis the followings are <strong>NOT</strong> considered as cardinal signs in osteoarthritis</p>	t	100	0	2018-07-06 06:48:58	2018-07-06 06:48:58	3	9db39e8488eff97a79f13ca40f3f7ed0
675	372	multiple-choice	<p>The possible diagnosis for this patient is?</p>	t	100	0	2018-07-06 06:53:41	2018-07-06 06:53:41	3	b8faea14bf082ded1952be9e14413127
676	372	multiple-choice	<p>What is the possible complication that may happen to this patient?</p>	t	100	0	2018-07-06 06:53:41	2018-07-06 06:53:41	3	c9b1a18933764893474d3662a59729c8
677	372	multiple-choice	<p>What will be the appropriate early management for the patient?</p>	t	100	0	2018-07-06 06:53:41	2018-07-06 06:53:41	3	00403667c54810fc16e042858fb17578
678	373	multiple-choice	<p>What is the stage of this tumor by the Musculoskeletal Tumor Society system</p>	t	100	0	2018-07-06 06:57:48	2018-07-06 06:57:48	3	b91c38d160ff904735f0b432dbba8435
679	373	multiple-choice	<p>The detailed description of the MRI showed the mass is mainly coming from the intramedullary expanding to the bony cortex. The pathologic findings suggest a high-grade lesion with characteristic spindle cells with pink staining osteoid matrix. Based on this description what is the type of osteosarcoma?</p>	f	100	0	2018-07-06 06:57:48	2018-07-06 06:57:48	3	219723331aea6cad346c32529dadbbd4
680	373	multiple-choice	<p>All of the following are known steps in the development of a malignant tumor with the ability to metastasize<strong> EXCEPT</strong>?&nbsp;</p>	t	100	0	2018-07-06 06:57:48	2018-07-06 06:57:48	3	8a8845ae30dea6df3883c255be00a6a0
681	374	essay	<div>\n<p>What is your diagnosis ? (30)</p>\n</div>	f	100	0	2018-07-13 09:34:01	2018-11-20 05:52:08	3	38d73f99cfac648b40495ef3b3dd73d5
682	374	essay	<p>Describe one of classifications of this injury? (40)</p>	f	100	0	2018-07-13 09:34:01	2018-11-20 05:52:08	3	0a726277e6755feb4ad68ac630cb7095
683	374	essay	<div>\n<p>List 3 complication of this injury treatment ! (30)</p>\n</div>	f	100	0	2018-07-13 09:34:01	2018-11-20 05:52:08	3	f8f6e6d5894973254fc3ff8398234b08
685	375	essay	<div>What is your diagnosis ?</div>	f	100	0	2018-07-13 10:21:08	2018-07-13 10:21:08	3	cf52eaa959727d1a72dae8a6f996cb1f
692	377	essay	<div>What structure is complicating the reduction of this injury ? (35)</div>\n\n<p>&nbsp;</p>	f	100	0	2018-07-14 09:24:16	2018-07-14 21:23:14	3	895e2207bc7bab7b346fd5a8fad292b2
697	378	essay	<div>What is the X-ray findings ? (20)</div>	f	100	0	2018-07-14 09:29:19	2018-07-14 21:23:31	3	f510f8bb81e1856a99033675416442f6
698	378	essay	<div>What is your diagnosis ? (25)</div>	f	100	0	2018-07-14 09:29:19	2018-07-14 21:23:31	3	f3acbd1d22718a500e312e92d0442d0d
699	378	essay	<div>What is the major vascularization of scaphoid ! (25)</div>	f	100	0	2018-07-14 09:29:19	2018-07-14 21:23:31	3	870ae023f41e3be56f762b6f0a094154
700	378	essay	<div>List 2 surgery options of this condition ! (30)</div>\n\n<p>&nbsp;</p>	f	100	0	2018-07-14 09:29:19	2018-07-14 21:23:31	3	ae280f8d904018fb05ccf6018f25f173
701	379	essay	<div>What is the presumed diagnosis ?</div>	f	100	0	2018-07-14 09:31:24	2018-07-14 21:21:21	3	e5fa30303db686aaf81ee1f611c60897
702	379	essay	<div>List 3 additional investigations to confirm diagnosis ?</div>	f	100	0	2018-07-14 09:31:25	2018-07-14 21:21:21	3	03bf93796444661dba75dc3c0f38d348
703	379	essay	<div>What are treatment options ?</div>\n\n<p>&nbsp;</p>	f	100	0	2018-07-14 09:31:25	2018-07-14 21:21:21	3	f5ae55ed4f9c0e011e44ea4ce5583497
704	380	essay	<div>What is the patient&rsquo;s underlying diagnosis? (20)</div>	f	100	0	2018-07-14 09:34:23	2018-07-14 21:22:08	3	53544f903a7f22cc5b653dda0a142e8c
705	380	essay	<div>What are the patient surgical options? (20)</div>	f	100	0	2018-07-14 09:34:23	2018-07-14 21:22:08	3	06177c61cbd135bf341bb08e5e913aab
706	380	essay	<div>List 4 basic concepts of tendon transfers! (30)</div>\n\n<div>&nbsp;</div>	f	100	0	2018-07-14 09:34:23	2018-07-14 21:22:08	3	ad921faab708fee3d150903697537ccf
707	380	essay	<div>What active functions are essential to restore, and what are the commonly used transfers to achieve this? (30)</div>	f	100	0	2018-07-14 09:34:24	2018-07-14 21:22:08	3	53a6636ee5d8be4db67762f5274a79d9
708	381	essay	<p>What is the possible cause of this condition (mention 4 answers) <strong>(</strong><strong>70)</strong></p>	f	100	0	2018-07-14 21:24:35	2018-07-14 21:26:26	3	e7edaf534115dd5a77d969763a9d0cad
709	381	essay	<p>What is the special test to determine patellar tracking during surgery? <strong>(</strong><strong>30)</strong></p>	f	100	0	2018-07-14 21:26:26	2018-07-14 21:26:26	3	57729ca532f462c62de2cd55588ed055
710	382	essay	<p>What is your clinical assesment? <strong>(</strong><strong>40)</strong></p>	f	100	0	2018-07-14 21:28:40	2018-07-14 21:28:40	3	bdab07e33175fca322eab4da8a27f204
711	382	essay	<p>How to reduce&nbsp; the risk factor pre operatively and intraoperatively? (minimal 4 answers) <strong>(</strong><strong>60)</strong></p>	f	100	0	2018-07-14 21:28:40	2018-07-14 21:28:40	3	5cb99d05eda20ff9b7fc9b9a0d5347d7
712	383	essay	<p>What is your diagnose ? <strong>(25)</strong></p>	f	100	0	2018-07-14 21:32:01	2018-07-14 21:32:01	3	ef74d7a81add0cbbc93fe48d73be9531
713	383	essay	<p>When is the best time to do surgery? <strong>(25)</strong></p>	f	100	0	2018-07-14 21:32:01	2018-07-14 21:32:01	3	5b2b1d9455fe930375c5e02a73d714df
714	383	essay	<p>What is the possible risk that will happen if the surgery was done immediately after the injury? <strong>(25)</strong></p>	f	100	0	2018-07-14 21:32:01	2018-07-14 21:32:01	3	90cf0775170c061a7b246eb081cb97c7
715	383	essay	<p>When do you treat conservatively? <strong>(25)</strong></p>	f	100	0	2018-07-14 21:32:01	2018-07-14 21:32:01	3	056282d43a8bc9fba13c8fb40ea0e4f8
843	438	essay	<p>are the bone graft need for this case? (25)</p>	f	100	0	2018-12-02 10:28:38	2018-12-02 10:35:53	3	c992afaf11244c8dc766908e7b39da7a
716	384	essay	<p>How do you prevent this condition ? (minimally 2 answers) <strong>(40)</strong></p>	f	100	0	2018-07-14 21:35:34	2018-07-14 21:35:34	3	968498daf9214cec07a0f561939be72b
717	384	essay	<p>What is the most fatal risk that may happened to this patient if left untreated? <strong>(20)</strong></p>	f	100	0	2018-07-14 21:35:34	2018-07-14 21:35:34	3	a6aeb0f271b7f3bd73cf9713d9bed829
718	384	essay	<p>Who are at risk for developing this condition post operatively? (minimally 3 answers) <strong>(40)</strong></p>	f	100	0	2018-07-14 21:35:34	2018-07-14 21:35:34	3	596b61d3ffc2743d505fd230c7399317
719	386	essay	<p><strong>Please describe the x-ray findings! (30)</strong></p>	f	100	0	2018-07-14 21:45:27	2018-07-15 08:22:06	3	bbd9520826a994dc850e59fdbadcfb2e
720	386	essay	<p><strong>What is the next investigation? (30)</strong></p>	f	100	0	2018-07-14 21:45:27	2018-07-15 08:22:06	3	e7cc68fa2f36f0e047dc17ef4ea901ca
721	386	essay	<p><strong>What is the goal of your investigation? (40)</strong></p>	f	100	0	2018-07-14 21:45:27	2018-07-15 08:22:06	3	02cff1daa2f800bb9aa667f9e11c5a13
722	387	essay	<p><strong>Please describe the x-ray findings! (30)</strong></p>	f	100	0	2018-07-15 08:21:34	2018-07-15 08:21:34	3	55c6224aaa7e285e9a1112c48a10e543
723	387	essay	<p><strong>What is your differential diagnosis? (30)</strong></p>	f	100	0	2018-07-15 08:21:34	2018-07-15 08:21:34	3	04a359db509e314b6cf97768dfa2c7a5
724	387	essay	<p><strong>Please mention the next investigation to establish the diagnosis! (20)</strong></p>	f	100	0	2018-07-15 08:21:34	2018-07-15 08:21:34	3	b70578d3e8617bda0b4278fba7ee9ba9
725	387	essay	<p><strong>What is your management plan? (20)</strong></p>	f	100	0	2018-07-15 08:21:34	2018-07-15 08:21:34	3	6cde4170f2662c7e10c690882273ea02
726	388	essay	<p><strong>Please describe the x-ray findings</strong><strong>! (25)</strong></p>	f	100	0	2018-07-15 08:44:19	2018-07-15 08:44:19	3	58cce096db1400397e9ae5c9b5cd871a
727	388	essay	<p><strong>Please describe the histopathology findings</strong><strong>! (25)</strong></p>	f	100	0	2018-07-15 08:44:19	2018-07-15 08:44:19	3	3a87280313514984aa7a271d259f45a1
728	388	essay	<p><strong>What is the diagnosis</strong><strong>? (25)</strong></p>	f	100	0	2018-07-15 08:44:19	2018-07-15 08:44:19	3	989e9a53bf973d0d9f3165393c96754b
729	388	essay	<p><strong>What are the surgical options</strong><strong>? (25)</strong></p>	f	100	0	2018-07-15 08:44:19	2018-07-15 08:44:19	3	e32b3e37b5ac394a5ce04283e63c7e4b
730	389	essay	<p><strong>Please describe the x-ray finding</strong><strong>! (25)</strong></p>	f	100	0	2018-07-15 08:46:53	2018-07-15 08:46:53	3	4f1bb5d0c6fb03f8643a2fc01895ffdc
731	389	essay	<p><strong>Please mention the </strong><strong>Mirel&rsquo;s</strong><strong> score for this </strong><strong>case (25)</strong></p>	f	100	0	2018-07-15 08:46:53	2018-07-15 08:46:53	3	d4dee13a2a7f0c8558de3d517150f5d4
732	389	essay	<p><strong>What is the most possible diagnosis</strong><strong>?(25)</strong></p>	f	100	0	2018-07-15 08:46:53	2018-07-15 08:46:53	3	ff2f52a2be992769f27131cad0b88943
733	389	essay	<p><strong>What is treatment recommendation</strong><strong>? (25)</strong></p>	f	100	0	2018-07-15 08:46:53	2018-07-15 08:46:53	3	b1a4469b73f5134881f0d7201f14a161
734	390	essay	<div>Please describe the histopathology findings in Figure A (30)</div>	f	100	0	2018-07-15 08:51:11	2018-07-17 00:47:22	3	e60c93a5ba111c4a40d33d4089c58f5d
735	390	essay	<div>What is the diagnosis in figure A? (20)</div>	f	100	0	2018-07-15 08:51:11	2018-07-17 00:47:22	3	247cc43622e73e3b8912be10234d932f
736	391	essay	<p><strong>What is your clinical </strong><strong>diagnosis ? (25)</strong></p>	f	100	0	2018-07-15 08:58:40	2018-07-15 08:58:40	3	19fb341f192b9efd4e660831a5412611
737	391	essay	<p><strong>Please name 3 major challenges in this </strong><strong>case! (30)</strong></p>	f	100	0	2018-07-15 08:58:40	2018-07-15 08:58:40	3	79b4c9678af83392bd9a828e6c0b18ee
738	391	essay	<p><strong>How do you manage this problem</strong><strong>? (25)</strong></p>	f	100	0	2018-07-15 08:58:40	2018-07-15 08:58:40	3	54de0564435f6780af37245170907d5a
739	391	essay	<p><strong>What type of reduction and stability required to overcome this problem</strong><strong>? What implant are you going to </strong><strong>use</strong><strong>? (20)</strong></p>	f	100	0	2018-07-15 08:58:40	2018-07-15 08:58:40	3	d6aed019d397b6b6369431d71f7e012c
740	392	essay	<p><strong>What is your clinical diagnosis? And please add the clasification for </strong><strong>your</strong> <strong>clinical</strong><strong> diagnosis (20)</strong></p>	f	100	0	2018-07-15 09:24:29	2018-07-15 09:26:03	3	1f52e269da8800e5c581e464a7a83668
741	392	essay	<p><strong>Please describe radiological abnormality you see in the x </strong><strong>ray</strong><strong> (30)</strong></p>	f	100	0	2018-07-15 09:24:29	2018-07-15 09:26:03	3	2d4793aa550334a7666acf4feb61156e
742	392	essay	<p><strong>How do you manage this problem</strong><strong>? (20)</strong></p>	f	100	0	2018-07-15 09:24:29	2018-07-15 09:26:03	3	bc2aebf86ea8bb48ac78d000f99c5538
743	392	essay	<p><strong>What do you think the prognosis of this case? Please explain </strong><strong>your</strong> <strong>answer</strong><strong> (30)</strong></p>	f	100	0	2018-07-15 09:24:29	2018-07-15 09:26:03	3	ab322380e102a51ac7b5b425e85a0e97
744	393	essay	<p><strong>What are radiological abnormalities seen on x </strong><strong>ray</strong> <strong>(30)</strong></p>	f	100	0	2018-07-15 09:35:24	2018-07-15 09:35:24	3	ef10abcd2ff9cc17030332ab6b11fe96
745	393	essay	<p><strong>What are the further laboratory work up required to established the diagnosis </strong><strong>(30)</strong></p>	f	100	0	2018-07-15 09:35:24	2018-07-15 09:35:24	3	a14b919935b9b9cd663d401216e3bb5c
746	393	essay	<p><strong>What is your clinical diagnosis? </strong><strong>(20)</strong></p>	f	100	0	2018-07-15 09:35:24	2018-07-15 09:35:24	3	68d0e2e4b797eacee24ebd6731bfb144
747	393	essay	<p><strong>What is your strategy to manage this case? </strong><strong>(20)</strong></p>	f	100	0	2018-07-15 09:35:24	2018-07-15 09:35:24	3	9778e96dbea4c113a887896f44983826
748	394	essay	<p><strong>What radiological abnormality seen on the pelvic x </strong><strong>ray</strong><strong> (30)</strong></p>	f	100	0	2018-07-15 09:38:52	2018-07-15 09:38:52	3	bf1d71c58ef5640ffc8f7f0e3bc5f9cd
749	394	essay	<p><strong>Please explain the patophysiology of the abnormality seen on the </strong><strong>pelvic</strong> <strong>xray</strong><strong> (30)</strong></p>	f	100	0	2018-07-15 09:38:52	2018-07-15 09:38:52	3	02f723c56b82e75693bfc961963b5533
750	394	essay	<p><strong>Meassurement shows that Reimers Index of the right hip is 60% while the left hip is 27%. What is you plan</strong><strong>? (40)</strong></p>	f	100	0	2018-07-15 09:38:52	2018-07-15 09:38:52	3	552282919dc47663b64ec914be40ebb0
751	395	essay	<p><strong>What radiological abnormality seen on the x ray of the right </strong><strong>femur</strong><strong>? (20)</strong></p>	f	100	0	2018-07-15 09:41:19	2018-07-15 09:41:19	3	a401711bfd273374ea9059cf0d982478
752	395	essay	<p><strong>Please describe classification using CT scan for </strong><strong>this</strong> <strong>case</strong><strong> (40)</strong></p>	f	100	0	2018-07-15 09:41:19	2018-07-15 09:41:19	3	fc6587f6062043dda962ec97bfbd6e1f
753	395	essay	<p><strong>What are the principles of treatment in </strong><strong>this</strong> <strong>case</strong><strong> (40)</strong></p>	f	100	0	2018-07-15 09:41:19	2018-07-15 09:41:19	3	2f9dbbb59835041e509e0e5bad33455b
754	396	essay	<p><strong>Please mention at least 3&nbsp; abnormalities&nbsp; in above CT </strong><strong>Scan </strong><strong>?</strong> <strong>(25)</strong></p>	f	100	0	2018-07-15 09:44:00	2018-07-15 09:44:00	3	cc7b33a76dfbcfe7c808ae313019bcc7
755	396	essay	<p><strong>What </strong><strong>is your diagnosis </strong><strong>? (25)</strong></p>	f	100	0	2018-07-15 09:44:00	2018-07-15 09:44:00	3	d5df18b87c3d2d8d042967f98964c82d
756	396	essay	<p><strong>Base on Denis Theory, please mention the spinal column are injured </strong><strong>? (25)</strong></p>	f	100	0	2018-07-15 09:44:00	2018-07-15 09:44:00	3	0a1b1b8865cf74db00cc695b835c8604
757	396	essay	<p><strong>What are treatment options </strong><strong>? (25)</strong></p>	f	100	0	2018-07-15 09:44:00	2018-07-15 09:44:00	3	680e7452decb99d268b0330667a56630
758	397	essay	<p><strong>What is the most possible mode of </strong><strong>injury? (25)</strong></p>	f	100	0	2018-07-15 09:51:42	2018-07-15 09:51:42	3	12fca94244ff366cf126b7d8ca785895
759	397	essay	<p><strong>Please explain the prognosis of hand </strong><strong>function ? (25)</strong></p>	f	100	0	2018-07-15 09:51:42	2018-07-15 09:51:42	3	6822c44077c0eb0cc67e498d041dba7d
760	397	essay	<p><strong>What is the most appropriate treatment for above case</strong><strong>? (25)</strong></p>	f	100	0	2018-07-15 09:51:42	2018-07-15 09:51:42	3	2d7afd6dcb1513e7b13ec2107a3bd93d
761	397	essay	<p><strong>What is the most common cervical alignment complication&nbsp; after posterior laminectomy in this case </strong><strong>? (25)</strong></p>	f	100	0	2018-07-15 09:51:42	2018-07-15 09:51:42	3	478c686397583868d1582f29641c5b5b
762	398	essay	<p><strong>What is the importance of the posterior spinal musculature in spinal </strong><strong>biomechanic</strong><strong> ? (25)</strong></p>	f	100	0	2018-07-15 09:54:00	2018-07-15 09:54:00	3	6bce28cb28f0e0269220756ad25676f0
763	398	essay	<p><strong>How much the percentage of load sharing in spinal column at anterior and posterior </strong><strong>collumn</strong><strong> (25)</strong></p>	f	100	0	2018-07-15 09:54:00	2018-07-15 09:54:00	3	72d031867774da2ed77bdecb14d37db1
764	398	essay	<p><strong>Please describe in brief the </strong><strong>Kirkardy</strong><strong> Willis cascade in degenerative </strong><strong>disc (25)</strong></p>	f	100	0	2018-07-15 09:54:00	2018-07-15 09:54:00	3	33c83e9ffc769194c3924b0f9aaae9eb
765	398	essay	<p><strong>Please mention 2 anatomical structures play role as a pain generator&nbsp; in </strong><strong>discogenic</strong> <strong>pain ! (25)</strong></p>	f	100	0	2018-07-15 09:54:00	2018-07-15 09:54:00	3	18786fce0089535edac5b330f9472cf6
766	399	essay	<p><strong>What is the abnormality do you find in CT Scan to&nbsp; support your </strong><strong>diagnosis (25)</strong></p>	f	100	0	2018-07-15 10:00:46	2018-07-15 10:00:46	3	4e4fd54b2b1179eb92d3323cad27b76e
767	399	essay	<p><strong>What is your plan to establish your&nbsp; </strong><strong>diagnosis (25)</strong></p>	f	100	0	2018-07-15 10:00:46	2018-07-15 10:00:46	3	5fc9aae07287eb95ee63302a2ca32836
768	399	essay	<p><strong>What is the most frequent bacterial pathogen cause this </strong><strong>problem (25)</strong></p>	f	100	0	2018-07-15 10:00:46	2018-07-15 10:00:46	3	94ef66b44cd4cdc79da3aa1c718ac0f3
769	399	essay	<p><strong>What is the most appropriate treatment option</strong><strong>? (25)</strong></p>	f	100	0	2018-07-15 10:00:46	2018-07-15 10:00:46	3	a10e7187a0f61279f4380010bcb614d1
770	400	essay	<p><strong>What is is the abnormality do you find in CT </strong><strong>Scan (30)</strong></p>	f	100	0	2018-07-15 10:02:31	2018-07-15 10:05:32	3	54788d6dae18421560d2606b1c7e45ac
771	400	essay	<p><strong>Why this problem can be happened </strong><strong>? (30)</strong></p>	f	100	0	2018-07-15 10:02:31	2018-07-15 10:05:32	3	3efffc72894d1f6b4ea70c7ccbda20a2
772	400	essay	<p><strong>What is your diagnosis </strong><strong>? (40)</strong></p>	f	100	0	2018-07-15 10:02:31	2018-07-15 10:05:32	3	445419e58a45cf833535d4627f401abb
773	385	essay	<p>What is the physical sign demonstrated? <strong>(25)</strong></p>	f	100	0	2018-07-15 11:26:16	2018-07-15 11:26:16	3	b79c3bed38cad1a443746f3fff61c259
774	385	essay	<p>What is the cause of the swelling <strong>(25)</strong></p>	f	100	0	2018-07-15 11:26:16	2018-07-15 11:26:16	3	b658130f683c24dfc191c03aaa594b10
775	385	essay	<p>What is the function of this tendon <strong>(25)</strong></p>	f	100	0	2018-07-15 11:26:16	2018-07-15 11:26:16	3	86d9a81c54b1f6569f2444aee45a40d4
776	385	essay	<p>What is your management? <strong>(25)</strong></p>	f	100	0	2018-07-15 11:26:16	2018-07-15 11:26:16	3	bbe84d5b1b13e912409248f308cf5be7
777	401	multiple-choice	<p>Figure below is the emergency department radiograph of a 7-year-old boy who has pain and is unwilling to use his right arm after a fall on the playground. What is the most appropriate initial treatment?</p>	t	100	0	2018-07-15 19:06:32	2018-07-15 19:06:32	3	baae85abbe516b5a7d320cbdbe277c33
778	402	multiple-choice	<p>A 12-year-old girl has had pain in her right knee for 1 month that started as activity-related and progressed to night pain. Radiographs are shown in Figures 16a and 16b, and a biopsy specimen is shown in Figure 16c. What is the recommended treatment?&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</p>	t	100	0	2018-07-15 19:09:04	2018-07-15 19:09:04	3	458426c73d4e44a2ca6de3092fe50691
779	403	multiple-choice	<p>Following clubfoot surgery, which of the following is the commonest residual deformity?</p>	t	100	0	2018-07-15 19:11:01	2018-07-15 19:12:12	3	4d9ca5964fbcdbe2c2fb086ba23676af
780	404	multiple-choice	<p>A 12-year-old girl presents with groin pain six months after treatment of a slipped capital femoral epiphysis. Preoperative radiographs are seen in Figure A, radiographs six months after in situ fixation are seen in Figure B. Which of the following is associated with the radiographic abnormality seen in Figure B?</p>	t	100	0	2018-07-15 19:13:56	2018-07-15 19:13:56	3	d558bc5d3fa7c3e2d81a44ba47d27591
782	406	multiple-choice	<p>A 60 year old, obese woman come with the chief complain knee pain which has been been there for 1 years. The x ray is as follow.</p>\n\n<p><strong>What abnormalities on x ray that can be seen</strong></p>	t	100	0	2018-07-15 19:23:30	2018-07-15 19:23:30	3	3fff0687ec41aeb9e7207a9d8ba2879f
783	408	multiple-choice	<p>What is your next diagnostic approach?</p>	t	100	0	2018-07-15 19:29:23	2018-07-15 19:29:23	3	62ffed93a289d97427b27bdb550b6d52
784	408	multiple-choice	<p>The diagnostic work up of the patient suggest a hip septic arthritis. How do you manage hip septic arthritis</p>	t	100	0	2018-07-15 19:29:23	2018-07-15 19:29:23	3	9a7f35934629d2e4d1273fec6e00c47f
785	408	multiple-choice	<p>Untreated proximal femur acute hematogenous osteomyelitis in children will cause hip septic arthritis which will destroy articular cartilage of the hip joint BECAUSE</p>	t	100	0	2018-07-15 19:29:23	2018-07-15 19:29:23	3	11ddbe4be900563d920c540e48d47e2b
789	409	multiple-choice	<p>What is your clinical diagnosis</p>	t	100	0	2018-07-15 19:36:54	2018-07-16 13:25:42	3	a541ed1c7a6d2199a52851fa9d2fc8b6
791	409	multiple-choice	<p>What is your next diagnostic work up</p>	t	100	0	2018-07-15 19:36:54	2018-07-16 13:25:42	3	a6d9fe7dfa61df5a6798008551f92cd3
792	409	multiple-choice	<p>How do you manage the problem</p>	t	100	0	2018-07-15 19:36:54	2018-07-16 13:25:42	3	f1486d6046d091bedb5735f0f96f40a7
793	409	multiple-choice	<p>The patophysiology for this condition</p>	t	100	0	2018-07-15 19:36:54	2018-07-16 13:25:42	3	cb9dff0b86b21b4cefb50aaf5bde92a9
794	409	multiple-choice	<p>What is the prognosis for this patient?</p>	t	100	0	2018-07-15 19:36:54	2018-07-16 13:25:42	3	7282590b81c94d647553d13d7c26c970
795	410	multiple-choice	<p>How do you manage his condition</p>	t	100	0	2018-07-16 13:32:55	2018-07-16 13:32:55	3	525fb517d823bcb394f1f0dbc91417af
796	410	multiple-choice	<p>What will happen to the left hip in the future if the left hip is left untreated</p>	t	100	0	2018-07-16 13:32:56	2018-07-16 13:32:56	3	f7cb590ea70bd6ec82c62bc4e770ab10
797	410	multiple-choice	<p>What is the major challenge for a Gross Motor Function Classification System(GMFCS) V, spastic child in the long term follow up</p>	t	100	0	2018-07-16 13:32:56	2018-07-16 13:32:56	3	08828e5c3bdb4488d733505ae498483f
798	411	multiple-choice	<p>How do you manage this problem</p>	t	100	0	2018-07-16 13:37:03	2018-07-16 13:37:03	3	4341379935be835a4f0a45035254882b
799	411	multiple-choice	<p>What is the devastating complication of this case</p>	t	100	0	2018-07-16 13:37:03	2018-07-16 13:37:03	3	80b8e67843bd4aa90404b822ca5f3669
800	412	multiple-choice	<p>What is your clinical diagnosis</p>	t	100	0	2018-07-16 13:42:08	2018-07-16 13:42:08	3	ebdb98e1cfca63d6bb2b86f59bd0dcc9
801	412	multiple-choice	<p>How do you manage this problem</p>	t	100	0	2018-07-16 13:42:08	2018-07-16 13:42:08	3	eb9a92006c2249fed01fad0833a8be69
802	412	multiple-choice	<p>This fracture is prone for late displacement because</p>	t	100	0	2018-07-16 13:42:08	2018-07-16 13:42:08	3	810839dbe2150778ecd9cd2ac0da8bf1
803	413	multiple-choice	<p>A 17 year old boy presented with mass and pain around the knee since 3 months before. The core biopsy had been done with&nbsp; HPE above</p>\n\n<p>The following statements about this tumor &nbsp;are true <strong>except</strong>&nbsp;that it</p>	t	100	0	2018-07-16 13:48:02	2018-07-16 13:48:02	3	d76610e5b5e0841e413738bab9370f5c
804	414	multiple-choice	<p>48-year-old male patient had a burst fracture of L1 after a motorcycle accident 6 hours previously. On physical examination at the lower back some percussion tenderness was detected around the thoracolumbar area but no skin abnormality was noted. Neurological examination revealed mild radicular symptoms on L2 area. No other organ injury was detected. X ray showed burst fracture involving superior end plate and retropulsed fragment. MRI showed intact PLC. The&nbsp; choice of treatment for this patient :</p>	t	100	0	2018-07-16 13:49:39	2018-07-16 13:49:39	3	5b42678bf274f460c62673e48fe25a24
805	415	multiple-choice	<p>Regarding the meniscus repair:</p>	t	100	0	2018-07-16 13:55:35	2018-07-16 13:55:35	3	3eb4a55ffa985a2c74761ad341712c9c
806	417	multiple-choice	<p>The figure above is the emergency department radiograph of a 7-year-old boy who has pain and is unwilling to use his right arm after a fall on the playground. What is the most appropriate initial treatment?</p>	t	100	0	2018-07-16 19:52:28	2018-07-16 19:52:28	3	7b0c074c0f7d8ef0e19dadf3f6694f48
807	418	multiple-choice	<p>What is the main pathological structure in the frozen shoulder</p>	t	100	0	2018-07-16 20:15:32	2018-07-16 20:15:32	3	a11a209265933ade32115cf2bca8558a
808	419	multiple-choice	<p>Varus intertrochanteric osteotomy for coxa valga commonly produces which of the following results?</p>	t	100	0	2018-07-16 20:18:28	2018-07-16 20:18:28	3	6cc5f513d58762b23db323e2a8caec85
809	420	multiple-choice	<p>Which of the following methods is considered effective in decreasing the dislocation rate following a total hip arthroplasty using a posterior approach to the hip?</p>	t	100	0	2018-07-16 20:22:00	2018-07-16 20:22:00	3	bc515dca145d499f3493d5ca0e342f0c
810	421	multiple-choice	<p>What complication is more likely following excessive medial retraction of the anterior covering structures during the anterolateral (Watson-Jones) approach to the hip?</p>	t	100	0	2018-07-16 20:24:06	2018-07-16 20:24:06	3	7152d752e759a982cb45a2f6fbd0083f
811	422	multiple-choice	<p>Figures 1A and 1B show the radiographs of a 51-year-old woman who injured her left leg after falling off a stepladder. Surgical reconstruction was performed with a compression screw and side plate; the postoperative radiograph is shown in Figure 1C. Following the gradual progression of weight bearing, she reports that she slipped again and placed full weight on the extremity. She now notes a new onset of increased pain in her left thigh and hip region. Follow-up radiographs are shown in Figures 1D and 1E. Reconstruction should consist of</p>	t	100	0	2018-07-16 20:27:09	2018-07-16 20:27:09	3	ec4d4eb7f984ca1eea64ac9e0e08e705
812	423	multiple-choice	<p>Which of the following organisms is most commonly isolated in acute necrotizing fasciitis?</p>	t	100	0	2018-07-16 20:30:27	2018-07-16 20:30:27	3	b0fa868cb4a058ee981785c7df1e29ff
813	424	multiple-choice	<p>Which of the following is considered the most common cause of a poor functional prognosis after an unstable posterior pelvic ring injury?</p>	t	100	0	2018-07-16 23:50:49	2018-07-16 23:50:49	3	a018712e1cfec7e4562c53976f200ac8
814	425	multiple-choice	<p>The figure above is the radiograph of a 68-year-old man who fell 3 weeks after undergoing a successful left primary total hip arthroplasty. He is experiencing a substantial increase in pain and an inability to bear weight. What is an appropriate treatment plan?</p>	f	100	0	2018-07-16 23:53:46	2018-07-17 00:00:50	3	cc0dc4259008c7f3619faefe99211908
815	426	multiple-choice	<p>A patient has a painful metal-on-metal (MOM) left total hip arthroplasty (THA). Which test(s) best correlate with prognosis if this patient is having a reaction to metal debris?</p>	t	100	0	2018-07-17 00:03:38	2018-07-17 00:03:38	3	bd8abdbfd2e9dfa37626e97a769a61b5
816	427	multiple-choice	<p>Biofilm is believed to play a major role in the pathogenesis of periprosthetic joint infection. Biofilm allows for the bacterial population to evade the effects of antimicrobial therapy primarily through</p>	t	100	0	2018-07-17 00:05:19	2018-07-17 00:05:19	3	007c245e7c4fffe846501a32cb64037d
817	428	multiple-choice	<p>A high risk for failure of a proximally porous-coated femoral component is associated with</p>	t	100	0	2018-07-17 00:07:13	2018-07-17 00:07:13	3	be5e610bfb75ec50dc8ccbef5391865b
818	429	multiple-choice	<p>Figures above are the radiographs of a 79-year-old woman with a 2-year history of progressively worsening right hip pain. She had a right total hip arthroplasty 7 years prior. An infection workup is negative. She option for revision surgery; the most appropriate surgical plan to address her femoral component is</p>	t	100	0	2018-07-17 00:10:43	2018-07-17 00:10:43	3	3cae3916151429679f77d83f982d89bf
819	430	multiple-choice	<p>A 68-year-old woman with diabetes, 6 weeks after uncemented THR, developed mild erythema and induration at the distal incision, no drainage. 2 weeks after drainage started turbid drainage from distal of the incision, mild surrounding erythema. Mild discomfort during hip motion. -ESR of 45 mm/h&nbsp; CRP of 54 mg/L. -PCR of the swab is positive for MRSA. No fluid obtained from hip joint What will you do after debridement?</p>	t	100	0	2018-07-17 00:14:36	2018-07-17 00:14:36	3	6f33aac3d3d5ce365f28761a8c6494ab
820	431	multiple-choice	<p>The figure above is the radiograph of a 71-year-old woman who had a right total hip arthroplasty 4 months ago; now she has tripped and fallen. She is unable to continue weight-bearing activity on her right leg but denies pain or ambulation issues prior to her fall. She is seen in the emergency department. What is the best treatment for this patient?<br />\n<br />\n&nbsp;</p>	t	100	0	2018-07-17 00:16:59	2018-07-17 00:16:59	3	ab6f96392421f7aadfc08f829edb200f
821	432	multiple-choice	<p>A 4 months old baby comes to your clinic with left eyelid drooping, decreased perspiration and pupillary constriction which occur on the same side of the face. The baby had a difficult labor during which his left shoulder was stuck during delivery. At this moment he cannot move his left upper and lower arm as well. What is your clinical diagnosis</p>	t	100	0	2018-07-17 00:19:34	2018-07-17 00:19:34	3	2bec3dac60d0d19a5efcfaef9169356e
822	390	essay	<p>Please describe the histopathology findings in the figure B (30)</p>	f	100	0	2018-07-17 00:47:22	2018-07-17 00:47:22	3	858c1b3c925502c0cd18a26ce2d57787
823	390	essay	<p>What is the diagnosis in figure B? (20)</p>	f	100	0	2018-07-17 00:47:22	2018-07-17 00:47:22	3	88c86e44a7fc84b5a1998dd6a3635bfb
824	433	essay	<p>Please mention the working diagnosis and differential diagnosis</p>	f	100	0	2018-08-04 08:07:01	2018-08-04 08:07:01	3	2bef508a991c242784a44248db2838ff
825	433	essay	<p>Please describe the clinical picture</p>	f	100	0	2018-08-04 08:07:01	2018-08-04 08:07:01	3	1aa2059750325ae91aca6d86a7a6c8d5
826	433	essay	<p>Please describe the sequence of the Ponseti method</p>	f	100	0	2018-08-04 08:07:01	2018-08-04 08:07:01	3	a69cb43b9f5ad4e2622ed3adde1b1929
827	434	essay	<p>List 3 differential diagnosis? (30)</p>	f	100	0	2018-12-02 10:12:02	2018-12-02 10:34:59	3	2800ed91d26816781e81afdc21bae9ad
828	434	essay	<p>What is specific test (clinically) for each of your differential diagnosis? (40)</p>	f	100	0	2018-12-02 10:12:02	2018-12-02 10:34:59	3	8a812abc47bd1514b0bb1aa746064d08
829	434	essay	<p>List dorsal compartments of the wrist! (30)</p>	f	100	0	2018-12-02 10:12:02	2018-12-02 10:34:59	3	ae3f3f4e4cc974ef98b173fd1b83ce89
831	435	essay	<p>What is most appropriate next treatment for this patient? (25)</p>	f	100	0	2018-12-02 10:16:04	2018-12-02 10:35:12	3	92356f63380dfc352c4ad9e48372c8a9
832	435	essay	<p>What is most likely structure if there is swelling over volar aspect of the thumb? (25)</p>	f	100	0	2018-12-02 10:16:04	2018-12-02 10:35:12	3	ac306199b8e486780dd44ebdace9414d
833	435	essay	<p>What structure is most vulnerable to injury during the operative procedure? (25)</p>	f	100	0	2018-12-02 10:16:04	2018-12-02 10:35:12	3	d1975f68fd05e6223e0c48746bb38a81
834	436	essay	<p>Describe xray findings! (40)</p>	f	100	0	2018-12-02 10:20:24	2018-12-02 10:35:28	3	3cbd6d3d735bf94a1542c48f20a01c2f
835	436	essay	<p>What is the most likely diagnosis? (30)</p>	f	100	0	2018-12-02 10:20:24	2018-12-02 10:35:28	3	dc9b94c5335742aacbec41641a34e184
836	436	essay	<p>What management should be offered to this patient? (30)</p>	f	100	0	2018-12-02 10:20:24	2018-12-02 10:35:28	3	daa442bb0a9abc0956555b3450809b57
838	437	essay	<p>List 2 operative technique for this case! (40)</p>	f	100	0	2018-12-02 10:24:39	2018-12-02 10:35:41	3	ac02063e816a9b34771b54e009fc4551
839	437	essay	<p>What type incision must be avoided? (30)</p>	f	100	0	2018-12-02 10:24:39	2018-12-02 10:35:41	3	8de1065869e70f0836670c145cdc1a02
840	438	essay	<p>What is your diagnosis ? (25)</p>	f	100	0	2018-12-02 10:28:38	2018-12-02 10:35:53	3	69ab5100da295e93cbd5fcfd75dc7029
841	438	essay	<p>Discuss the possibility causing this condition ? (25)</p>	f	100	0	2018-12-02 10:28:38	2018-12-02 10:35:53	3	b932133e6a1605ae8e1eadd9bc78ab3c
842	438	essay	<p>Please explain the principal surgical step for this case ? (25)</p>	f	100	0	2018-12-02 10:28:38	2018-12-02 10:35:53	3	6681c93fe0f4cb55f9e3ef345978e59f
844	439	essay	<p>What is others physical findings you will expected &nbsp;found at the weakness side (right side extremities)? (30)</p>	f	100	0	2018-12-02 11:27:19	2018-12-02 11:27:19	3	1f39d1c7bd194698c1510351f029ee34
845	439	essay	<p>What is &nbsp;physical findings you will expected&nbsp; found at the normal side of the motoric power&nbsp; (left side extremities)? (30)</p>	f	100	0	2018-12-02 11:27:19	2018-12-02 11:27:19	3	4cba31bbcc2cbbe022bf641f181c1221
846	439	essay	<p>Please describe the cause and &nbsp;the &nbsp;type (name) of spinal cord injury? (40)</p>	f	100	0	2018-12-02 11:27:19	2018-12-02 11:27:19	3	16d6b03f8507efac9d9759fd99edfa5a
847	440	essay	<p>Please describe at least 3&nbsp; abnormal findings in MRI result! (25)</p>	f	100	0	2018-12-02 11:30:34	2018-12-02 11:30:34	3	f24e4a0f6e82a98263f989c49459f81f
848	440	essay	<p>Please explain the indication for&nbsp; surgery in this abnormality? (25)</p>	f	100	0	2018-12-02 11:30:34	2018-12-02 11:30:34	3	0f23aa0147235c82f34434fddb32d61b
849	440	essay	<p>Why the patient having pain and weakness? (25)</p>	f	100	0	2018-12-02 11:30:34	2018-12-02 11:30:34	3	7f7ec9b9b7312277a6ed77cfcad77e69
850	440	essay	<p>What is the best patient position to ease your surgical procedure! (25)</p>	f	100	0	2018-12-02 11:30:34	2018-12-02 11:30:34	3	7300ec5f453d53c4e91aa88deb97a1f8
851	441	essay	<p>Please explain the most possible mode of injury of this spinal column injury! (25)</p>	f	100	0	2018-12-02 11:33:20	2018-12-02 11:33:20	3	6445bde9a80c0a2dfbf86cc85b7becf6
852	441	essay	<p>Please explain&nbsp; about the biomechanical&nbsp; role of posterior ligamentous complex (PLC) in spine! (25)</p>	f	100	0	2018-12-02 11:33:20	2018-12-02 11:33:20	3	ffe1f8baf25eeefa5cdfdaf3fe16a5a0
853	441	essay	<p>What is the effect in injury of PLC! (25)</p>	f	100	0	2018-12-02 11:33:20	2018-12-02 11:33:20	3	f94c7683e1d04b43fb2e23deac0844ca
854	441	essay	<p>How do you you treat this patient! (25)</p>	f	100	0	2018-12-02 11:33:20	2018-12-02 11:33:20	3	e8f588fc61f440a9783e4b529a36fa1f
855	442	essay	<p>Biomechanically , what is the mode of injury&nbsp; ?&nbsp; (25)</p>	f	100	0	2018-12-02 11:36:18	2018-12-02 11:36:18	3	10c27724154ffa2d7825bc856d298395
856	442	essay	<p>In open mouth AP x-ray, when this fracture can be considered as an unstable pattern ? (25)</p>	f	100	0	2018-12-02 11:36:18	2018-12-02 11:36:18	3	d453cb468804ac61a42e1a2d00f26229
857	442	essay	<p>What structure have important roles in the stability&nbsp; of C1-C2 ? (25)</p>	f	100	0	2018-12-02 11:36:18	2018-12-02 11:36:18	3	fcfad753140309af36e1fe22cccb2671
858	442	essay	<p>How do you managed this case in stable type of fracture &nbsp;? (25)</p>	f	100	0	2018-12-02 11:36:18	2018-12-02 11:36:18	3	5d5556a071eb84c20672faf20d46ade0
859	443	essay	<p>Please describe at least 3&nbsp; x-ray abnormalities? (25)</p>	f	100	0	2018-12-02 11:41:49	2018-12-02 11:41:49	3	edddd6e3173143784fd4bca90d99d5ae
860	443	essay	<p>What is your diagnosis and differential diagnosis? (25)</p>	f	100	0	2018-12-02 11:41:49	2018-12-02 11:41:49	3	4f1cce67152b9c6ee88fa3d247752698
861	443	essay	<p>What is others imaging modalities will you proposed? (25)</p>	f	100	0	2018-12-02 11:41:49	2018-12-02 11:41:49	3	451ba59f48a277201d62b64c1b150d2e
862	443	essay	<p>What is your next step to established your diagnosis? (25)</p>	f	100	0	2018-12-02 11:41:49	2018-12-02 11:41:49	3	f076b2b43d454f614a185e7b6d354286
863	444	multiple-choice	<p>Which of the followings is correct regarding the steps from neoplastic process to metastatic deposits in bone:</p>	t	100	0	2018-12-02 19:22:18	2018-12-02 19:22:18	3	34e40ddf7a4bdcfcb25fe6f7b2d94110
864	445	multiple-choice	<p>Which of the following orders are correct regarding the survival rates of these neoplasms from best to worst:</p>	t	100	0	2018-12-02 19:24:00	2018-12-02 19:24:00	3	c53ae189b415be90abf64b3838136184
865	446	multiple-choice	<p>These statements regarding metastatic bone disease treatment &nbsp;are correct, <strong>except</strong>:</p>	t	100	0	2018-12-02 19:25:57	2018-12-02 19:25:57	3	c440ed83b9d5fbb86894565e247d837e
866	447	multiple-choice	<p>The major drawback on using liquid nitrogen as an intraoperative adjuvant in treating giant cell tumor of the bone is:</p>	t	100	0	2018-12-02 19:27:35	2018-12-02 19:27:35	3	51dacd303d5d99affd640124ff4b6221
867	448	multiple-choice	<p>Denosumab is a drug that has been indicated for treatment of giant cell tumor of the bone in the US since 2014, given as a monthly injection. This drug acts as :</p>	t	100	0	2018-12-02 19:29:02	2018-12-02 19:29:02	3	4f9587b25049c18797e1c8623d5e5211
868	449	multiple-choice	<p>The following statements regarding giant cell tumor of the bone are true, <strong>except</strong>:</p>	t	100	0	2018-12-02 19:30:31	2018-12-02 19:30:31	3	02ef6391625a7828765377a06cc3539a
869	450	multiple-choice	<p>The following syndromes are associated to osteosarcoma, <strong>except</strong>:</p>	t	100	0	2018-12-02 19:32:39	2018-12-02 19:32:39	3	728ec6c2089fdb618d0e2ae52990b8cc
870	451	multiple-choice	<p>The most sensitive physical examination for ACL Rupture is..?</p>	t	100	0	2018-12-02 19:34:14	2018-12-02 19:34:14	3	d864e08a58712b07b85b67bc6cffc274
871	452	multiple-choice	<p>Habitual knee cap Dislocation with increase insall savati ratio is best treated by</p>	t	100	0	2018-12-02 19:35:53	2018-12-02 19:35:53	3	3d43182226aa155071830573971ffe4f
872	453	multiple-choice	<p>The Possible diagnosis for this baby is ?</p>	t	100	0	2018-12-02 19:39:27	2018-12-02 19:39:27	3	dff12de78660a2ec1708ed0667bcb933
873	453	multiple-choice	<p>The Disease is caused by :</p>	t	100	0	2018-12-02 19:39:27	2018-12-02 19:39:27	3	41f84e8e8366402e5f9489983f9fd768
874	454	multiple-choice	<p>A 5 year old boy complain pain on his right hip and abnormal gait. Physical examination shows alimited abduction on right hip. No History of trauma. Plain Radiograph shows ostonecrosis on right femur head.Possible diagnosis is :</p>	t	100	0	2018-12-02 19:45:24	2018-12-02 19:45:24	3	40a1582d55879c01eb02b5818d0aada4
875	455	multiple-choice	<p>The following statements are true for hangman&rsquo;s fracture, <strong>except</strong> :</p>	t	100	0	2018-12-02 19:47:07	2018-12-02 19:47:07	3	cadafec5ce1cf36a77d89cce8e0d6d1f
902	470	multiple-choice	<p>A 52-year-old woman who fell down the stairs and sustained an acute hemarthrosis of the elbow. The radiograph are shown below.&nbsp;</p>\n\n<p>What is the most common complication following surgical treatment of this injury?</p>	t	100	0	2018-12-03 14:31:09	2018-12-03 14:31:09	3	db3da6baebf4b9092bc91c057b19296a
876	456	multiple-choice	<p>A 45 year old man presents to outpatient clinic with difficulty ambulating and buttoning his shirt. It started 2 years ago but has worsened significantly this last 10 months. On physical exam he is unable to perform a tandem gait and has a positive Hoffman&rsquo;s sign bilaterally, however he has no clonus and a down-going Babinski bilaterally. He has 4/5 strength in his hands, but 5/5 strength in all other muscle groups. The MRI shows the axial of C4/5 and C5/6.</p>\n\n<p>What is the appropriate treatment :</p>	t	100	0	2018-12-02 19:59:07	2018-12-02 19:59:07	3	4a6225e1fe7265790a8b2f11221d1e90
690	377	essay	<div>What is the x-ray findings ? (35)</div>	f	100	0	2018-07-14 09:24:15	2018-07-14 21:23:14	3	fcd61ae8294b1aa14b0610952f98a24d
877	457	multiple-choice	<p>The Pucker sign refer to?</p>	t	100	0	2018-12-02 20:05:01	2018-12-02 20:05:01	3	3bbd9e70b6e86b774216bf4d2584ca13
878	457	multiple-choice	<p>Which of the following mechanism is most related with the above diagnosis?</p>	t	100	0	2018-12-02 20:05:01	2018-12-02 20:05:01	3	ba007da74b766c21cfbf517caaf3988c
879	457	multiple-choice	<p>Which of the following findings may related to the most common neurological injury related to the above diagnosis</p>	t	100	0	2018-12-02 20:05:01	2018-12-02 20:05:01	3	5745a6b4385f3113507e3bd3089bda90
880	457	multiple-choice	<p>In occult condition of the same diagnosis with a minimal displacement on the fractured area, you may find the Fat Pad sign that is correlated with?</p>	t	100	0	2018-12-02 20:05:01	2018-12-02 20:05:01	3	4b9e91e8093854baae3d38c22110d8b3
881	458	multiple-choice	<p>To assess the fracture personality in the coronal plane you must also assess?</p>	t	100	0	2018-12-02 20:14:15	2018-12-02 20:14:15	3	4f50222b9290445b85b019a24f1d06c2
882	459	multiple-choice	<p>Before you give bisphosphonate you ask the patient to check for her BMD, which of the findings that suggest osteoporotic condition?</p>	t	100	0	2018-12-02 20:17:12	2018-12-02 20:17:12	3	c43e0ccfa84f94fc4d9f43ae77f30fd7
883	460	multiple-choice	<p>Some of the BMD investigation also present you with Z score. Which of the following suggest more fragile condition to the patient&rsquo;s bone?</p>	t	100	0	2018-12-02 20:18:51	2018-12-02 20:18:51	3	65535497a6f78c22bf217c80f75fe277
884	461	multiple-choice	<p>After 4 years of routine bisphosphonates consumption, the patient fell sudden intense of pain on her thigh while waking up from her bed in the morning. The femur x ray showed thickening of the cortex and discontinuity in the proximal third of the shaft.</p>\n\n<p>What is the appropriate diagnosis?</p>	t	100	0	2018-12-02 20:21:26	2018-12-02 20:21:26	3	1465ddf9f14b9bec080ce55db7a40819
885	462	multiple-choice	<p>The possible mechanism for the spine injury is?</p>	t	100	0	2018-12-02 20:28:35	2018-12-02 20:28:35	3	7e15063787df93e643ea9804aaa2a17a
886	462	multiple-choice	<p>What is the TLICS score for this patient, and what will be the treatment recommendations</p>	t	100	0	2018-12-02 20:28:35	2018-12-02 20:28:35	3	3f53310b06b8305c7bf440bfcef65039
887	462	multiple-choice	<p>Related with the above diagnosis you are planning to perform a decompression, stabilization and fusion. The senior advice you to put long construct stabilization of the injury because of the highly unstable condition.</p>\n\n<p>Which of the following construct match the recommendations?</p>	t	100	0	2018-12-02 20:28:35	2018-12-02 20:28:35	3	92ca2756b61f3adb1872b278daa5069c
888	462	multiple-choice	<p>Three days after the injury the patient complain severe pain on the abdomen, you also notice tense and distended abdomen, tenderness and loss of bowel movement.</p>\n\n<p>What is the possible diagnosis?</p>	t	100	0	2018-12-02 20:28:35	2018-12-02 20:28:35	3	2007178c2f87fb04e20be1314ab8911e
889	463	multiple-choice	<p>From the x ray you notice callus formation on the 2nd and 3rd Right metatarsal neck without obvious fracture line. Which of the diagnosis match her condition?</p>	f	100	0	2018-12-02 20:37:07	2018-12-19 11:52:06	3	7ffe20ad868ad569266124354fa6f177
890	463	multiple-choice	<p>What will you suggest to the patient?</p>	f	100	0	2018-12-02 20:37:07	2018-12-19 11:52:06	3	1a3ca745d1be699469d265836b1b3f01
891	463	multiple-choice	<p>Despite the main complain, the patient look extremely lean with BMI of 16 and complain of eating disorder, related to this particular finding what condition should you also check and evaluate?</p>	f	100	0	2018-12-02 20:37:07	2018-12-19 11:52:06	3	9cc3cbaed5a2adfe235d227651aeeca3
892	463	multiple-choice	<p>The possible condition of amenorhea in professional female athlete may related with?</p>	f	100	0	2018-12-02 20:37:07	2018-12-19 11:52:06	3	ef6badeabda1dbb47e830592c2a59a18
893	463	multiple-choice	<p>The positive findings of above evaluations (eating disorder, amenorrhea and osteopenia) will suggest you to what diagnosis?</p>	f	100	0	2018-12-02 20:37:07	2018-12-19 11:52:06	3	c39e940e379cc82d65f6f8c4c7c189c0
894	464	multiple-choice	<p>Related with the above condition the most common structural problem related with patient&rsquo;s complain are?</p>	t	100	0	2018-12-03 14:13:31	2018-12-03 14:13:31	3	0fcda4e44ebbc11908c78b1a590a39ea
895	464	multiple-choice	<p>Cortical depression in the posterolateral head of the humerus that resulted from forceful impaction of the humeral head against the anteroinferior glenoid rim when the shoulder is dislocated anteriorly is known as?</p>	t	100	0	2018-12-03 14:13:31	2018-12-03 14:13:31	3	76ca0f5e4262ea51fb80240b58ce4c77
896	465	multiple-choice	<p>The above condition is possibly related with?</p>	t	100	0	2018-12-03 14:17:40	2018-12-03 14:17:40	3	b091e62b7d74d75e54e64e2b0f09cbb1
897	465	multiple-choice	<p>Lower branch of brachial plexus injury is true in?</p>	t	100	0	2018-12-03 14:17:41	2018-12-03 14:17:41	3	d967c5b52da9ea5e0ddc64c1e501617a
898	466	multiple-choice	<p>Erb&rsquo;s point is located at the juction of..?</p>	t	100	0	2018-12-03 14:20:23	2018-12-03 14:20:23	3	423ed2ee94e8f1636336708d1845ef18
899	467	multiple-choice	<p>A 19 year old collegiate baseball player injures the ring finger on his dominant hand while sliding headfirst into second base. He reports that he is unable to actively flex or extend the distal interphalangeal joint of the finger. Radiographs are shown below.</p>\n\n<p>What is the anatomic lesion leading to this injury?</p>	t	100	0	2018-12-03 14:24:56	2018-12-03 14:24:56	3	d18d727cbd9e6f8a72ca8c03d16d6ab9
900	468	multiple-choice	<p>A 40 year old man sustains a fracture-dislocation of C4-5. Examination reveals no motor or sensory function below the C5 level. All extremities are areflexic. The bulbocavernosus reflex is absent. The prognosis for this patient&rsquo;s neurologic recovery can be best determined by..?</p>	t	100	0	2018-12-03 14:26:52	2018-12-03 14:26:52	3	53ceb4554cf63ea8644dab42114352f5
901	469	multiple-choice	<p>A 27 year old man who was involved in a high-speed motor vehicle crash arrives at the trauma center with loss of consciousness, multiple posterior rib fractures, a left scapula body fracture, a left humerus fracture, bilateral femoral shaft fractures, and an open right ankle fracture-dislocation. Initial vital signs are a blood pressure of 88/50 mm Hg, a pulse of 120 bpm, and respirations of 22/min. His injury severity score is 28 and lactate levels are 2.7. CT scans of the head and abdomen are negative for hemorrhage, and after initial resuscitation the patient is cleared for surgery.</p>\n\n<p>Initial orthopaedic management should consist of d&eacute;bridement and irrigation of the right ankle with..?</p>	t	100	0	2018-12-03 14:29:00	2018-12-03 14:29:00	3	1bf0c00653cea7645a07428703203c7a
988	520	essay	<p>Named two radiological signs that suggest malignant transformation! (25)</p>	f	100	0	2018-12-17 02:23:40	2018-12-17 02:23:40	3	108ceb798315480fd62d909fbf86ec77
989	520	essay	<p>Named a syndrome associated with multiple lesions! (25)</p>	f	100	0	2018-12-17 02:23:40	2018-12-17 02:23:40	3	7034515f3ae7e5e67e3a6a8aeb887931
6	4	multiple-choice	<p>Titanium and its alloys are unsuitable candidates for which of the following implant application?</p>	f	100	0	2017-07-09 21:54:22	2017-07-09 21:54:22	3	5d8b9691a2fd0647b417a0401db5b120
7	6	multiple-choice	<p>Why is tendon considered an anisotropic material?</p>	f	100	0	2017-07-09 22:19:45	2017-07-09 22:19:45	3	39640d5b9706cee5d548e939ec225f94
8	7	multiple-choice	<p>Which of following changes to heart rate, blood pressure, and bulbocavernosus reflex is typical of spinal shock?</p>	f	100	0	2017-07-09 22:22:41	2017-07-09 22:22:41	3	7ed6bc6b45dab1efe83fa154f2152390
9	8	multiple-choice	<p>What is the primary intracellular signaling mediator for bone morphogenic protein (BMP) activity ?</p>	f	100	0	2017-07-09 22:24:50	2017-07-09 22:24:50	3	c79dd46c9514c9aa58623d816c003619
1	1	multiple-choice	<p style="text-align: justify">Figures 1 and 2 show the postreduction radiographs obtained from a 32-year-old man who fell from a ladder onto his outstretched right arm. He reports right wrist pain and dense numbness in his radial digits. What is the most appropriate treatment option?&nbsp;</p>	t	100	1	2025-11-04 13:03:53	2025-11-04 22:10:18	3	XzVQeVvO
1081	738	multiple-choice	<p>When evaluating a patient with suspected purulent flexor tenosynovitis in the thumb, the distal forearm and little finger are found to be swollen as well. The most likely anatomic explanation is the existence of a potential space in which of the following?</p>	f	100	1	2025-11-04 20:14:00	2025-11-04 22:12:19	3	BrRdbK92
10	9	multiple-choice	<p>Examination of an obese 3-year-old-girl reveals 30 degrees of unilateral genu varum. A radiographic of the involved leg with the patella forward in shown in the figure above. Management should consist of ?</p>	f	100	0	2017-07-09 22:25:39	2017-07-09 22:27:00	3	a7b1f295965068e635f74552a9ec78d9
11	10	multiple-choice	<p>Immobilization of human tendons leads to what changes in structure and/or function?</p>	f	100	0	2017-07-09 22:28:45	2017-07-09 22:28:45	3	b260bc19f82f82256bbe729a92d2a6aa
1082	739	multiple-choice	<p style="text-align: justify"><strong>Vignette:</strong> A 25-year-old man came to the emergency room complaining of wounds on the middle finger and ring finger of his left hand due to being hit by glass 1 hour before being admitted to the hospital. He can flex his fingers except his middle finger. The sensory for middle finger: ulnar site is hiposthesia and normal at radial site. The sensory for ring finger is normal at radial and ulnar site. CRT at all fingers less than 2 seconds.</p>\n\n<p style="text-align: justify"><strong>Question:</strong> From clinical picture, what zone the wound located?</p>	f	100	1	2025-11-04 20:00:00	2025-11-04 20:00:00	3	\N
1083	740	multiple-choice	<p style="text-align: justify"><strong>Vignette:</strong> Same as question 1</p>\n\n<p style="text-align: justify"><strong>Question:</strong> What structure may be injured at middle finger?</p>	f	100	1	2025-11-04 20:01:00	2025-11-04 20:01:00	3	\N
1084	741	multiple-choice	<p style="text-align: justify"><strong>Vignette:</strong> A 75-year-old woman underwent dynamic hip screw (DHS) fixation for a right intertrochanteric fracture 6 weeks ago. She now complains of persistent thigh pain, difficulty weight-bearing, and limb shortening. Follow-up X-ray shows collapse of the fracture site with lag screw migration through the femoral head.</p>\n\n<p style="text-align: justify"><strong>Question:</strong> Which mechanism explains this?</p>	f	100	1	2025-11-04 20:08:00	2025-11-04 20:08:00	3	\N
1085	742	multiple-choice	<p style="text-align: justify"><strong>Vignette:</strong> Same as question 9</p>\n\n<p style="text-align: justify"><strong>Question:</strong> What is the best management for this patient?</p>	f	100	1	2025-11-04 20:09:00	2025-11-04 20:09:00	3	\N
1087	744	multiple-choice	<p style="text-align: justify">A 72-year-old man has had three operations for an infected total knee arthroplasty over the past 2 years. Despite two-stage revisions and long-term antibiotics, he continues to have pain, swelling, and occasional pus discharge. He now has poor soft tissue coverage, a stiff knee, and low functional demand (walks with a walker). What is the best management option now?</p>	f	100	1	2025-11-04 20:16:00	2025-11-04 22:12:47	3	zAR6Xm9L
1088	745	multiple-choice	<p style="text-align: justify">A 70-year-old man had a chronic periprosthetic joint infection after total knee arthroplasty (TKA), caused by Staphylococcus epidermidis. He was treated 3 months ago with first-stage surgery — implant removal, extensive debridement, and insertion of an antibiotic-loaded cement spacer, followed by 6 weeks of IV antibiotics. He now returns for reassessment before planned second-stage revision TKA. On examination the wound is well healed, no sinus, no redness or swelling, Knee is painless, spacer intact, no systemic symptoms. Laboratory tests show: ESR: 18 mm/h, CRP: 4 mg/L (both normalized), Joint aspiration: clear fluid, WBC <1000/µL, culture negative. What is the next best management step?</p>	f	100	1	2025-11-04 20:17:00	2025-11-04 22:12:47	3	dxVBDJqn
1089	746	multiple-choice	<p style="text-align: justify"><strong>Vignette:</strong> A 17-year-old man came with a complaint of being unable to straighten his left middle finger, a problem he had experienced since childhood. There was no history of trauma. There was a history of normal delivery. There was no family history of similar problems. The patient was a student with right hand dominant. From physical examination there is a flexion deformity of PIP joint with non correctable deformity, normal strength and sensation at middle finger. No history of pain.</p>\n\n<p style="text-align: justify"><strong>Question:</strong> What is the possible diagnosis?</p>	f	100	1	2025-11-04 20:18:00	2025-11-04 22:12:47	3	XzVQodqv
1090	747	multiple-choice	<p style="text-align: justify"><strong>Vignette:</strong> Same as question 19</p>\n\n<p style="text-align: justify"><strong>Question:</strong> What is the pathophysiology for this abnormality?</p>	f	100	1	2025-11-04 20:19:00	2025-11-04 22:12:47	3	WjqNXwV8
1091	748	multiple-choice	<p style="text-align: justify">A 13-year-old boy was evaluated for leg length difference. His pelvis balanced when a 1-inch (2.54 cm) block was placed under his left foot. History revealed he had a left distal femur physeal fracture treated with casting at age 10. Radiographs show normal limb alignment, but his left distal femoral physis is closed and his left femur is 2.5 cm shorter than the right. All other physes are open. His bone age is equal to his chronologic age. What surgical treatments will best equalize his discrepancy?</p>	f	100	1	2025-11-04 20:20:00	2025-11-04 22:12:47	3	ndqYw4VZ
1092	749	multiple-choice	<p style="text-align: justify">Figure 1 is the radiograph of a 3-year-old female who was referred to the office by her pediatrician for evaluation of a leg-length discrepancy. Pregnancy and birth were uncomplicated, and medical history includes no chronic disease conditions. The patient has met all developmental milestones. Physical examination shows a positive Galeazzi sign and limited hip abduction on the left. Examination of the upper extremity and spine shows no abnormalities. The patient shows no signs of pain and walks with a nonantalgic short leg limp. What is the best next step?</p>	f	100	1	2025-11-04 20:21:00	2025-11-04 22:12:47	3	r2q4bvV0
1094	751	multiple-choice	<p style="text-align: justify">A 15-year-old boy with mild type I osteogenesis imperfecta (OI) has a midshaft radius/ulna fracture that is in bayonet apposition with loss of the radial bow and 40-degree apex volar and ulnar angulation. Closed reduction improves the angulation to 20 degrees; the bayonet apposition and loss of radial bow remains. His contralateral forearm has a normal appearance upon examination. What is the best treatment for this fracture?</p>	f	100	1	2025-11-04 20:23:00	2025-11-04 22:12:47	3	lrqvYD9D
1095	752	multiple-choice	<p style="text-align: justify">Figure 1 is the radiograph of a 4-year-old girl who is being evaluated for genu varum. She has a family history of bowed legs and short stature. She has a mutation in the PHEX gene. Identify the laboratory studies most consistent with this diagnosis?</p>	f	100	1	2025-11-04 20:24:00	2025-11-04 22:12:47	3	b3q58n9o
3	2	multiple-choice	<p>A 51-year-old woman has shoulder pain after a minor fall. A radiograph, MRI scan, are seen in Figures 1 through 2. Biopsy specimens are seen in Figures 3. What is the most likely diagnosis?</p>	t	100	1	2025-11-04 13:06:37	2025-11-04 13:08:47	3	ndqY89Z0
1106	763	multiple-choice	<p style="text-align: justify">Following intra-articular fracture, the risk of post traumatic arthritis is correlated strongly with what risk factor?</p>	f	100	1	2025-11-04 20:35:00	2025-11-04 22:12:16	3	lrqvJdqD
1107	764	multiple-choice	<p style="text-align: justify">Figure below shows a radiograph of an active 30-year-old man who sustained an injury to his ring finger 1 week earlier. The most appropriate treatment is</p>	f	100	1	2025-11-04 20:36:00	2025-11-04 22:12:16	3	b3q5Db9o
1108	765	multiple-choice	<p style="text-align: justify">A fracture of the hook of hamate is confirmed. You decide to proceed with open reduction and internal fixation with a headless screw. Which structure is at risk of injury during surgery?</p>	f	100	1	2025-11-04 20:37:00	2025-11-04 22:12:16	3	BlqlYxRw
1109	766	multiple-choice	<p style="text-align: justify">A 45-year-old man who is a smoker has a significant hemothorax and bilateral closed femoral fractures. On insertion of a chest tube, 1,100 mL of blood was returned. He has had 75 mL of chest tube output over the last 2 hours while being resuscitated in the ICU. His base deficit is now 2 and his urine output has been 3 mL/kg over the last hour. What is the next most appropriate step in management?</p>	f	100	1	2025-11-04 20:38:00	2025-11-04 22:12:16	3	O69bgGqP
1096	753	multiple-choice	<p style="text-align: justify">A 16-year-old male who presents with a conventional high-gradeosteosarcoma of the left proximal tibia. Figures below are the anteroposterior and lateral radiographs and MRI scan (axial and sagital). Staging studies show no evidence of metastatic disease. At which stage is this patient according to the Enneking Musculoskeletal Tumor Society (MSTS) staging system for malignant bone tumors?</p>	f	100	1	2025-11-04 20:25:00	2025-11-04 22:12:16	3	Blql0Eqw
1097	754	multiple-choice	<p style="text-align: justify">A 14-year-old female presents with vague worsening pain in her left knee that has worsened over the past 3 months. She has refrained from sports and running because of the pain. She denies any inciting traumatic event or any recent illness. Radiographs are obtained and shown in Figure below. Which of the following diagnostic tests serves as the most appropriate next step in management?</p>	f	100	1	2025-11-04 20:26:00	2025-11-04 22:12:16	3	O69b5zVP
1098	755	multiple-choice	<p style="text-align: justify">A 47-year-old male presents with swelling and pain in the right wrist. The symptoms have progressed over the last 6 months and she has noted a decreased range of motion of the wrist joint. Figures below are the radiographs and MRI of the right wrist and Figure D shows the histology. What is the best treatment option for this patient?</p>	f	100	1	2025-11-04 20:27:00	2025-11-04 22:12:16	3	OxV8JdqK
1099	756	multiple-choice	<p style="text-align: justify">A 25-year-old female presents with knee pain. Radiographs and histopathology are shown in Figures below. What is the most likely diagnosis and recommended staging study for further evaluation?</p>	f	100	1	2025-11-04 20:28:00	2025-11-04 22:12:16	3	BW9XG4Vy
1100	757	multiple-choice	<p style="text-align: justify">A 65-year-old female presents to your office with left thigh pain that has been worsening over the last several months. She reports that pain is worse with weight-bearing and sometimes wakes her up at night. On radiological examination of the femur, a lytic lesion was seen. Lytic bone lesions, commonly seen in metastatic bone disease or multiple myeloma, are due to which of the following mechanisms</p>	f	100	1	2025-11-04 20:29:00	2025-11-04 22:12:16	3	dxVBXjqn
1101	758	multiple-choice	<p style="text-align: justify">A 6-year-old child with diplegic cerebral palsy has maximum hip abduction of 20 degrees, a negative Thomas test, and mild hip subluxation as demonstrated by a migration index of 20%. The best treatment is bilateral</p>	f	100	1	2025-11-04 20:30:00	2025-11-04 22:12:16	3	XzVQQeVv
1102	759	multiple-choice	<p style="text-align: justify">A 3-month-old infant presents to your clinic with difficulty moving his extremities. The child had an uneventful prenatal course and birth, but since birth he has had poor head control and difficulty feeding. On physical exam, he is able to move all extremities, but he moves his upper extremities more than his lower extremities, and his hands more than his shoulders. He has no spasticity, but global weakness. The patient's deep tendon reflexes are absent, but he has tongue fasciculations. What is the cellular pathology and prognosis for this patient?</p>	f	100	1	2025-11-04 20:31:00	2025-11-04 22:12:16	3	WjqNDyV8
1103	760	multiple-choice	<p style="text-align: justify">A 40-year-old man falls onto his right arm and complains of the inability to extend his right elbow. The injury is closed, and he is neurovascularly intact in the injured extremity. Radiographs show a 2 cm displaced transverse olecranon fracture. The muscle that causes the deforming force is innervated by a nerve that comes off of what structure of the brachial plexus?</p>	f	100	1	2025-11-04 20:32:00	2025-11-04 22:12:16	3	ndqYb8RZ
1104	761	multiple-choice	<p style="text-align: justify">Figures 1 and 2 are the radiographs of a 6-year-old male who is brought to the emergency department after he sustained an isolated femoral shaft fracture after being struck by a car while riding a scooter. What is the most appropriate treatment?</p>	f	100	1	2025-11-04 20:33:00	2025-11-04 22:12:16	3	r2q4wl90
1105	762	multiple-choice	<p style="text-align: justify">An 86-year-old female presents to the emergency department because she has had pain and inability to ambulate after she fell from a standing height. Medical history includes atrial fibrillation, diabetes mellitus, and pulmonary fibrosis requiring home oxygen. Anteroposterior and lateral radiographs as well as an axial CT scan of the left hip are shown in Figures 1 through 3. When considering surgical options for this patient, the most likely benefit of hemiarthroplasty over internal fixation is a decrease in</p>	f	100	1	2025-11-04 20:34:00	2025-11-04 22:12:16	3	EW9Z8eRm
1111	768	multiple-choice	<p style="text-align: justify">Which of the following findings best describes the acetabular fracture shown in Figure below?</p>	f	100	1	2025-11-04 20:40:00	2025-11-04 22:12:35	3	BW9Xlz9y
4	3	multiple-choice	<p>A skeletally-mature 14-year-old girl presents with her parents to your clinic with a "lump" near her knee. She is very bothered by the appearance of her knee and it is very painful when she bumps the palpable prominence. An AP x-ray is shown in figures 1, respectively. She has no other similar lesions elsewhere in her body, and her parents are unaware of any relevant family history. What is the next best step in management?&nbsp;</p>	t	100	1	2025-11-04 13:11:59	2025-11-04 13:12:42	3	r2q4lq0l
1121	778	multiple-choice	<p style="text-align: justify">Through 4 are the radiograph, magnetic resonance images, and photomicrograph of a biopsy specimen from a 42-year-old man with an insidious onset of left hip pain. Further imaging reveals no other lesions. What is the most appropriate initial treatment?</p>	f	100	1	2025-11-04 20:50:00	2025-11-04 22:12:35	3	vMqGzX97
1122	779	multiple-choice	<p style="text-align: justify">Antiosteoporosis medication should be considered for which of the following patients?</p>	f	100	1	2025-11-04 20:51:00	2025-11-04 22:12:35	3	j7Rwo7RG
1113	770	multiple-choice	<p style="text-align: justify">Figure 1 shows the radiograph obtained from a 76-years old woman who has sharp pain in her groin, thigh, and buttocks that worsens with activity. She has been dealing with this pain for more than 1 year but is otherwise healthy. Recently, she has begun to notice night pain. The pain no longer responds to NSAIDs. She would like to be able to dance at her daughter's wedding in 4 months and wonders how best to proceed. What is the best next step?</p>	f	100	1	2025-11-04 20:42:00	2025-11-04 22:12:35	3	rP9J619A
1114	771	multiple-choice	<p style="text-align: justify">Figures below are the radiographs of a 25-year-old woman whose pain has progressed during the last several years to pain with any activity and pain at night. What is the most appropriate treatment?</p>	f	100	1	2025-11-04 20:43:00	2025-11-04 22:12:35	3	KoRovwqM
1115	772	multiple-choice	<p style="text-align: justify">Figures below are the right femur radiograph and bone scan of a 71-year-old man with long-standing metastatic prostate cancer who has experienced increasing right thigh pain for 2 months. The pain is worse with activity and is alleviated with rest. He experienced similar pain in his left thigh 18 months ago and subsequently sustained a left subtrochanteric femur fracture after a low-energy twisting injury. He was successfully treated with an intramedullary nail. He had been receiving zoledronic acid for 4 years prior to the fracture. This patient's history includes heavy steroid use. His current symptoms are most likely the result of:</p>	f	100	1	2025-11-04 20:44:00	2025-11-04 22:12:35	3	g49LG5VO
1116	773	multiple-choice	<p style="text-align: justify">Figure 8 is the bilateral standing alignment radiograph of a 2-year-old boy who has bowed legs. His mother states that he was born with bowed legs, and the deformity seems to have worsened since he started walking at 11 months of age. The metaphyseal-diaphyseal angles are 18 bilaterally. What is the best treatment option?</p>	f	100	1	2025-11-04 20:45:00	2025-11-04 22:12:35	3	bKVz0Qq4
1117	774	multiple-choice	<p style="text-align: justify">Which bacterial stage describes free-floating bacteria that bind to an inert substrate allowing for apoptosis and the creation of a biofilm matrix?</p>	f	100	1	2025-11-04 20:46:00	2025-11-04 22:12:35	3	ZnR0gkqd
1118	775	multiple-choice	<p style="text-align: justify">A 50-year-old female has been diagnosed with osteomyelitis of her left tibia. The infection is isolated to the medullary canal of the bone, and her past medical history is significant for heavy smoking and chronic venous stasis. Based on the clinical staging of osteomyelitis, what would be her Cierney-Mader classification?</p>	f	100	1	2025-11-04 20:47:00	2025-11-04 22:12:35	3	kLRgDkVx
1119	776	multiple-choice	<p style="text-align: justify">Figures A-D are the radiographs and CT images from a 32-year-old diabetic man who was treated with an intramedullary nail following an open tibial shaft fracture 13-months ago. He continues to have pain in the leg. Despite a course of IV antibiotics, his laboratory markers remain elevated. What is depicted by the arrow in figure D?</p>	f	100	1	2025-11-04 20:48:00	2025-11-04 22:12:35	3	E4qeGlV3
1120	777	multiple-choice	<p style="text-align: justify">A 2-year-old girl has L3-L4–level myelomeningocele. She is a full community ambulator with ankle-foot orthoses. She has had swelling and erythema of her right proximal tibia for 1 week. Her temperature is 37.7C. Radiographs show a mild periosteal reaction. Her erythrocyte sedimentation rate (ESR) is 40 mm/hr (reference range [rr], 0 to 20 mm/hr), and her C-reactive protein (CRP) level is 0.8 mg/L ([rr], 0.08 to 3.1 mg/L). What is the best next step?</p>	f	100	1	2025-11-04 20:49:00	2025-11-04 22:12:35	3	xm9KKM9G
1123	780	multiple-choice	<p style="text-align: justify">A researcher is working on Medication A, a drug FDA-approved for the treatment of osteoporosis in men and women. It is an anti-resorptive agent that inhibits the formation, function and survival of osteoclasts. It does not bind to calcium hydroxyapatite. At 1-year after the initial dose, tissue levels are non-detectable. It can be used in the presence of cancer metastases to bone. What is Medication A?</p>	f	100	1	2025-11-04 20:52:00	2025-11-04 22:12:35	3	gE9DJpRX
1124	781	multiple-choice	<p style="text-align: justify">A 25-year-old male presents to the emergency department after a lawnmower accident with traumatic loss of his great toe. On examination, his wound is grossly contaminated with soil. In addition to a cephalosporin and an aminoglycoside, penicillin is given. Which of the following is true with regards to the organism that penicillin is targeting in this injury?</p>	f	100	1	2025-11-04 20:53:00	2025-11-04 22:12:35	3	yPVn6eVD
1125	782	multiple-choice	<p style="text-align: justify">A 32-year-old female who presents to the trauma bay after falling off a roof. Paramedics state that she was initially complaining of back pain, but she now appears irritable and lethargic. She has received 2 liters of crystalloid since arriving in the trauma bay. Her blood pressure is now 76/42. A Foley catheter is placed, and her urine output is 12 ml/hour. What additional finding would suggest hemorrhagic shock over neurogenic shock?</p>	f	100	1	2025-11-04 20:54:00	2025-11-04 22:12:35	3	52q3GGqe
1128	785	multiple-choice	<p style="text-align: justify">A farmer is seen in the emergency department after falling out from tree onto the rice field. He is unable to bear weight. Exploration of a 0.5 cm laceration over the anterior tibia reveals bone. Radiographs reveal oblique displaced midshaft tibial and fibular fractures. Based on these findings, what is the most appropriate antibiotic prophylaxis?</p>	f	100	1	2025-11-04 20:57:00	2025-11-04 22:12:13	3	8d9kNDV0
1129	786	multiple-choice	<p style="text-align: justify">During an anterior approach to the shoulder for a reverse total shoulder arthroplasty (TSA) with a concomitant latissimus dorsi/teres major transfer, retractors are placed along the superficial surface of the latissimus dorsi. Which nerve is most at risk during exposure?</p>	f	100	1	2025-11-04 20:58:00	2025-11-04 22:12:13	3	p3V1e3VZ
1131	788	multiple-choice	<p style="text-align: justify">A 42-year-old woman sustained a closed talar neck fracture in a motor vehicle accident. Which of the following is an avoidable complication of surgical treatment?</p>	f	100	1	2025-11-04 21:01:00	2025-11-04 22:12:13	3	5M9p5lq6
1132	789	multiple-choice	<p style="text-align: justify">A 20-year-old man used his fist to hit another man in the mouth. Examination 3 hours after the injury shows a 1-cm laceration over the third metacarpophalangeal joint. Treatment should consist of which of the following?</p>	f	100	1	2025-11-04 21:02:00	2025-11-04 22:12:13	3	YMqxLYqL
1133	790	multiple-choice	<p style="text-align: justify">A 2-year-old boy will not bear weight after tripping over a curb. He is afebrile. Laboratory studies show a WBC count of 6,000/mm3 (normal 3,500 to 10,500/mm3) and an erythrocyte sedimentation rate of 10 mm/h (normal up to 20 mm/h). Examination reveals reproducible tenderness over the midshaft of the right tibia. AP and lateral radiographs of the right femur and tibia shows no obvious fracture line. What is the next most appropriate step in management?</p>	f	100	1	2025-11-04 21:03:00	2025-11-04 22:12:13	3	JyVA8dRl
1134	791	multiple-choice	<p style="text-align: justify">A 23-year-old man has an isolated open tibial fracture without distal neurologic or vascular compromise following a motorcycle accident. After undergoing skeletal stabilization and several debridements, a clean 6x6-cm wound remains over the anteromedial surface of the distal third of the tibia. The tibia is exposed throughout the length of the wound and the periosteum has been stripped. What is the best option for wound management at this time?</p>	f	100	1	2025-11-04 21:04:00	2025-11-04 22:12:13	3	7X9EoJRG
1135	792	multiple-choice	<p style="text-align: justify">A 25-year-old man has a midshaft femoral fracture with 25% comminution and is undergoing closed intramedullary nailing. Proximal locking is performed uneventfully; however, during distal locking screw insertion, only one of the screws is noted to have bone purchase. Which of the following procedures is the best solution to this problem?</p>	f	100	1	2025-11-04 21:05:00	2025-11-04 22:12:13	3	ljqyzQRB
1136	793	multiple-choice	<p style="text-align: justify">A 30-year-old man who sustained a tibial fracture with a peroneal nerve palsy 2 years ago now has foot drop and weak eversion of the foot. He reports success with stretching exercises, but he catches his toes when his foot tires. Examination reveals that the foot is plantigrade and supple. What is the most appropriate next step in management?</p>	f	100	1	2025-11-04 21:06:00	2025-11-04 22:12:13	3	Bx9OnAVe
1137	794	multiple-choice	<p style="text-align: justify">A 12-year-old boy with a femoral fracture is planned to undergo closed reduction and stabilization using Titanium elastic nail. Upon measurement, the isthmus is 12 mm. What diameter of nail that is the best for that size?</p>	f	100	1	2025-11-04 21:07:00	2025-11-04 22:12:13	3	BrRdKOq2
1138	795	multiple-choice	<p style="text-align: justify">What is the advantage of medial and lateral crossed pins compared to two lateral pins in the treatment of supracondylar humerus fractures?</p>	f	100	1	2025-11-04 21:08:00	2025-11-04 22:12:13	3	mgqrbeqn
1139	796	multiple-choice	<p style="text-align: justify">Regarding the meniscus repair:</p>	f	100	1	2025-11-04 21:09:00	2025-11-04 22:12:13	3	KdVW5eqn
1140	797	multiple-choice	<p style="text-align: justify">Which of the following is considered the most common cause of a poor functional prognosis after an unstable posterior pelvic ring injury?</p>	f	100	1	2025-11-04 21:10:00	2025-11-04 22:12:28	3	2MRMwY9J
1141	798	multiple-choice	<p style="text-align: justify">48-year-old male patient had a burst fracture of L1 after a motorcycle accident 6 hours previously. On physical examination at the lower back some percussion tenderness was detected around the thoracolumbar area but no skin abnormality was noted. Neurological examination revealed mild radicular symptoms on L2 area. No other organ injury was detected. X ray showed burst fracture involving superior end plate and retropulsed fragment. MRI showed intact PLC. The choice of treatment for this patient</p>	f	100	1	2025-11-04 21:11:00	2025-11-04 22:12:28	3	2K92KbqP
1142	799	multiple-choice	<p style="text-align: justify">A 23-year-old woman is involved in a motorcycle accident. She sustains bilateral femur fractures (Abbreviated Injury Score [AIS]=3), an intra-abdominal injury (AIS=3), facial fractures (AIS=2), and a pulmonary injury (AIS=2). What is her Injury Severity Score (ISS)?</p>	f	100	1	2025-11-04 21:12:00	2025-11-04 22:12:28	3	nwqjQY9B
1143	800	multiple-choice	<p style="text-align: justify">A 27 year old man who was involved in a high-speed motor vehicle crash arrives at the trauma center with loss of consciousness, multiple posterior rib fractures, a left scapula body fracture, a left humerus fracture, bilateral femoral shaft fractures, and an open right ankle fracture-dislocation. Initial vital signs are a blood pressure of 88/50 mm Hg, a pulse of 120 bpm, and respirations of 22/min. His injury severity score is 28 and lactate levels are 2.7. CT scans of the head and abdomen are negative for hemorrhage, and after initial resuscitation the patient is cleared for surgery. Initial orthopaedic management should consist of débridement and irrigation of the right ankle with?</p>	f	100	1	2025-11-04 21:13:00	2025-11-04 22:12:28	3	zAR6nwRL
1156	813	multiple-choice	<p style="text-align: justify">What sign or symptom may occur with cubital tunnel syndrome that DOES NOT occur with Guyon neuropathy?</p>	f	100	1	2025-11-04 21:26:00	2025-11-04 22:11:55	3	KbRmBWVz
1157	814	multiple-choice	<p style="text-align: justify">Which of the following factors is most likely to contribute to pseudarthrosis in a patient who has undergone a single-level anterior decompression and fusion procedure for the treatment of cervical radiculopathy?</p>	f	100	1	2025-11-04 21:27:00	2025-11-04 22:11:55	3	rP9Jr19A
1158	815	multiple-choice	<p style="text-align: justify">A 70-year-old woman has knee osteoarthritis which no longer manageable nonsurgically. Radiographs reveal a 30-degree mechanical axis deformity. When using the measured resection technique during total knee arthroplasty (TKA), the best way to avoid femoral malrotation is to reference the:</p>	f	100	1	2025-11-04 21:28:00	2025-11-04 22:11:55	3	KoRoNwqM
1159	816	multiple-choice	<p style="text-align: justify">Which of the following is considered the primary indication for surgery for patient with hallux valgus..?</p>	f	100	1	2025-11-04 21:29:00	2025-11-04 22:11:55	3	g49Lr5RO
1160	817	multiple-choice	<p style="text-align: justify">A 73-years-old woman who return for her annual follow-up 14 years after undergoing total hip arthroplasty. The patient begins to experience pain, and a decision is made to proceed with surgical intervention. When performing a posterior approach to the hip, which structure protects the anterior retractor from causing damage to the femoral neurovascular structure?</p>	f	100	1	2025-11-04 21:30:00	2025-11-04 22:11:55	3	bKVzJQV4
1161	818	multiple-choice	<p style="text-align: justify">A 35-year-old man who has had a 6-month history of low back pain and tenderness now reports worsening pain and stiffness in the hips and entire back. An AP radiograph of the pelvis demonstrates a fusion of the sacroiliac joint bilaterally. What is the next most appropriate step in management?</p>	f	100	1	2025-11-04 21:31:00	2025-11-04 22:11:55	3	ZnR0ykVd
1162	819	multiple-choice	<p style="text-align: justify">Which of the following symptoms is common in the early stages of osteoarthritis (OA) of the elbow?</p>	f	100	1	2025-11-04 21:32:00	2025-11-04 22:11:55	3	kLRgKkRx
1163	820	multiple-choice	<p style="text-align: justify">A patient with a bone mineral density (BMD) T-score of -2.6 would be considered to have</p>	f	100	1	2025-11-04 21:33:00	2025-11-04 22:11:55	3	E4qeglV3
1144	801	multiple-choice	<p style="text-align: justify">A 40 year old man sustains a fracture-dislocation of C4-5. Examination reveals no motor or sensory function below the C5 level. All extremities are areflexic. The bulbocavernosus reflex is absent. The prognosis for this patient's neurologic recovery can be best determined by?</p>	f	100	1	2025-11-04 21:14:00	2025-11-04 22:12:28	3	dxVBrjVn
1145	802	multiple-choice	<p style="text-align: justify">The most critical factor in the prevention of chronic infection following severe open tibia fracture..?</p>	f	100	1	2025-11-04 21:15:00	2025-11-04 22:12:28	3	XzVQre9v
1146	803	multiple-choice	<p style="text-align: justify">What is the mechanism of antimicrobial action of aminoglycoside antibiotics..?</p>	f	100	1	2025-11-04 21:16:00	2025-11-04 22:12:28	3	WjqNryq8
1147	804	multiple-choice	<p style="text-align: justify">Which of the following is the most likely predictor of lower extremity amputation in diabetic foot disease?</p>	f	100	1	2025-11-04 21:17:00	2025-11-04 22:12:28	3	ndqY68RZ
1148	805	multiple-choice	<p style="text-align: justify">A 46-year-old woman has an 18-month history of plantar heel pain. She describes start-up symptoms that persist with activity throughout the day. Night splinting, custom inserts, cortisone injections, and physical therapy have failed. She has neutral-slight valgus hindfoot alignment. There is point tenderness over the plantar medial heel, a negative Tinel sign result, and a plantar heel spur as seen on radiographs. Ankle dorsiflexion is 15° less than neutral with the knee in extension and 10° with the knee in flexion. In addition to the treatment of the plantar fascia, what is the most appropriate next step?</p>	f	100	1	2025-11-04 21:18:00	2025-11-04 22:12:28	3	r2q4rlV0
1149	806	multiple-choice	<p style="text-align: justify">A 12-year-old boy at 160 cm, 82 kg comes to the outpatient clinic with 2 months of left anterior knee pain without discrete injury. He has a slightly antalgic gait on the left, has a full knee range of motion, stable ligaments, and mild tenderness at his tibial tubercle. He has restricted hip internal rotation and worsening knee pain with hip motion. Knee radiographs are negative. What is the best next step in management?</p>	f	100	1	2025-11-04 21:19:00	2025-11-04 22:12:28	3	EW9Z5eVm
1150	807	multiple-choice	<p style="text-align: justify">The orthopedic surgeon also found positive (+) Rheumatoid Factor (RF) in this patient. The following description is TRUE related with RF</p>	f	100	1	2025-11-04 21:20:00	2025-11-04 22:12:28	3	lrqvbdqD
1151	808	multiple-choice	<p style="text-align: justify">A 50-year-old carpenter has chronic pain over the lateral aspect of the elbow. He notes pain when using a hammer. On exam, he has pain with resisted wrist extension while the elbow is fully extended. Which muscle attachment is likely to be involved?</p>	f	100	1	2025-11-04 21:21:00	2025-11-04 22:12:28	3	b3q5Abqo
1152	809	multiple-choice	<p style="text-align: justify">A 34-year-old female has an insidious onset of heel pain when first getting out of bed and at the end of the day after prolonged standing. She works as a waitress and recently had bariatric surgery with a current BMI of 35. The physical exam is notable for tenderness with direct palpation of the anteromedial heel. She has a gastrocnemius contracture noted on Silverskiold testing. What is the most likely diagnosis?</p>	f	100	1	2025-11-04 21:22:00	2025-11-04 22:12:28	3	Blql3x9w
1153	810	multiple-choice	<p style="text-align: justify">A patient with rheumatoid arthritis has a rupture of the extensor digitorum communis to the fourth and fifth metacarpals. You are planning to perform an extensor indicis proprius (EIP) tendon transfer. What effect will which have on index finger extension?</p>	f	100	1	2025-11-04 21:23:00	2025-11-04 22:12:28	3	O69bLGRP
1154	811	multiple-choice	<p style="text-align: justify">A 63-year-old man has a feeling of generalized clumsiness in his arms and hands, difficulty buttoning his shirt, and gradually worsening gait instability. During examination, his neck is gently passively flexed to end range while he is seated. The patient describes an electric shock-like sensation that radiates down the spine and into the extremities. This describes which of the following?</p>	f	100	1	2025-11-04 21:24:00	2025-11-04 22:12:28	3	OxV8A49K
1164	821	multiple-choice	<p style="text-align: justify">A 73-year-old woman has back and legs pain. Imaging reveals a lumbar degenerative scoliosis. Nonsurgical management, consisting of physical therapy, medications, and injections, has failed. During the surgical planning, dual-energy x-ray absorptiometry performed, and her T-score returns as -2.6. Intraoperative options to help reduce the risk of instrumentation failure include</p>	f	100	1	2025-11-04 21:34:00	2025-11-04 22:11:55	3	xm9KrMVG
1165	822	multiple-choice	<p style="text-align: justify">Decreased sun exposure leads to decreased bone health via what mechanism?</p>	f	100	1	2025-11-04 21:35:00	2025-11-04 22:11:55	3	vMqGrX97
1166	823	multiple-choice	<p style="text-align: justify">Compared with myodesis, osteomyoplasty offers which of the following advantages in transtibial amputation?</p>	f	100	1	2025-11-04 21:36:00	2025-11-04 22:11:55	3	j7Rw87VG
1167	824	multiple-choice	<p style="text-align: justify">A 20-year-old dancer has an atraumatic onset of midfoot pain. Radiographic findings are normal. Her body mass index is 18.5, and she has had 5 menstrual cycles during the past year. What is the long-term risk of no treatment?</p>	f	100	1	2025-11-04 21:37:00	2025-11-04 22:11:55	3	gE9DrpRX
1168	825	multiple-choice	<p style="text-align: justify">What is the best reason to use an autograft (rather than an allograft) for anterior cruciate ligament (ACL) reconstruction in a young athlete?</p>	f	100	1	2025-11-04 21:38:00	2025-11-04 22:11:55	3	yPVnQeRD
1169	826	multiple-choice	<p style="text-align: justify">Reimplantation of a traumatically amputated limb requires all of the following EXCEPT:</p>	f	100	1	2025-11-04 21:39:00	2025-11-04 22:11:55	3	52q3AGVe
1155	812	multiple-choice	<p style="text-align: justify">Marfan syndrome is an autosomal dominant disorder which results from a defective gene encoding for</p>	f	100	1	2025-11-04 21:25:00	2025-11-04 22:11:55	3	BW9XZzRy
1126	783	multiple-choice	<p style="text-align: justify">A 37-year-old male arrives to the trauma slot following a high-speed motorcycle crash. His Glasgow Coma score is 14 and his only orthopaedic injury is exhibited in Figure A. His current vital signs are a BP of 90/60, HR 120, and a lactate of 2.5 mMol/L. Other findings include a grade 1 splenic laceration and bilateral pulmonary contusions seen on chest radiograph. Which of the following has been suggested as an indication to perform damage control orthopedic care?</p>	f	100	1	2025-11-04 20:55:00	2025-11-04 22:12:13	3	oER7BEq2
1127	784	multiple-choice	<p style="text-align: justify">A 39-year-old female presents with the following motor vehicle crash with the injury seen in Figure A (immobilized in a pelvic binder). The iatrogenic neurologic injury most commonly caused by placement of the anterior construct for this injury, as shown in Figure B, would cause which of the following?</p>	f	100	1	2025-11-04 20:56:00	2025-11-04 22:12:13	3	j8qa0r9W
1130	787	multiple-choice	<p style="text-align: justify">A 25-year-old man is brought to the emergency department following a motor vehicle accident. Extrication time was 2 hours, and in the field, he had a systolic blood pressure by palpation of 90 mmHg. Intravenous therapy was started, and on arrival to the emergency department his systolic blood pressure is 90 mmHg with a pulse rate of 130. Examination reveals a flail chest and a femoral diaphyseal fracture. Ultrasound of the abdomen is positive. The trauma surgeons take him to the operating room for an exploratory laparotomy. At the conclusion of the procedure, systolic pressure of 100 mmHg with a pulse rate of 110. Oxygen saturation is 90% on 100% oxygen, and the patient's temperature is 35 Celcius degrees. What is the recommended treatment of the femoral fracture at this time?</p>	f	100	1	2025-11-04 21:00:00	2025-11-04 22:12:13	3	03VPBNqO
1110	767	multiple-choice	<p style="text-align: justify">A 20-year-old man is brought to the emergency department after a high-speed motor vehicle accident. His initial blood pressure is 70/40 mm Hg. He is currently receiving intravenous fluids as well as blood. His Focused Assessment with Sonography for Trauma examination did not show any free fluid in his abdomen and his chest radiograph is unremarkable. An AP pelvis radiograph is shown in Figure below. What is the next most appropriate step in the management of his pelvic injury?</p>	f	100	1	2025-11-04 20:39:00	2025-11-04 22:12:18	3	OxV8Q49K
1170	738	multiple-choice	<p>When evaluating a patient with suspected purulent flexor tenosynovitis in the thumb, the distal forearm and little finger are found to be swollen as well. The most likely anatomic explanation is the existence of a potential space in which of the following?</p>	f	100	1	2025-11-04 22:12:19	2025-11-04 22:12:19	3	oER7EE92
1112	769	multiple-choice	<p style="text-align: justify">A 14-year-old boy sustained a 100% displaced distal radius Salter-Harris type II fracture. Neurologic examination demonstrates normal motor examination and two-point discrimination. He undergoes fracture reduction to the anatomic position with the application of a long arm cast. Postreduction he reports increasing hand and wrist pain with diminution of two-point discrimination to 10 mm over the index and middle fingers over the next several hours after surgery. The cast is bivalved and the padding released relieving all external pressure over the arm. Reevaluation reveals increasing sensory deficit over the affected area. What is the next most appropriate management intervention?</p>	f	100	1	2025-11-04 20:41:00	2025-11-04 22:12:35	3	KbRm4Wqz
1093	750	multiple-choice	<p style="text-align: justify">A 60-year-old woman complains of hip pain and weakness for 6 months. She has difficulty rising from a chair and walks with a waddling gait. X-ray pelvis shows pseudofractures (Looser's zones) at the femoral neck. Lab tests: low vitamin D, low phosphate, high alkaline phosphatase. What is the most likely diagnosis?</p>	f	100	1	2025-11-04 20:22:00	2025-11-04 22:12:47	3	EW9ZD7Rm
1086	743	multiple-choice	<p style="text-align: justify">A 70-year-old man comes with persistent pain and swelling in his right knee for 6 months. He had a total knee arthroplasty (TKA) done 4 years ago for osteoarthritis. He now notices a small sinus with discharge near the old surgical scar. He does not have fever, but walking is painful. On examination found sinus tract with mild pus discharge, knee warm and tender, painful range of motion, no gross instability. Laboratory marker: ESR: 70 mm/h, CRP: 30 mg/L. Joint aspiration: cloudy fluid, WBC 35,000/µL (85% neutrophils). Culture: Staphylococcus epidermidis. X-ray: radiolucent line around tibial component, but no implant loosening. What is the best management for this patient?</p>	f	100	1	2025-11-04 20:15:00	2025-11-04 22:12:47	3	nwqjLkVB
1055	723	multiple-choice	<p style="text-align: justify">A 63-year-old man has a feeling of generalized clumsiness in his arms and hands, difficulty buttoning his shirt, and gradually worsening gait instability. During examination, his neck is gently passively flexed to end range while he is seated. The patient describes an electric shock-like sensation that radiates down the spine and into the extremities. This describes which of the following?&nbsp;</p>	t	100	1	2025-11-04 14:00:13	2025-11-04 14:00:22	3	BW9X34Vy
446	231	essay	<div>Describe about Mirels classification (35)</div>	f	100	0	2017-12-27 14:24:46	2017-12-27 14:27:31	3	e8d41d4a6c6015cdf904a0e432aa7c6e
1061	724	multiple-choice	<p>From clinical picture, what zone the wound located?</p>	f	100	1	2025-11-04 20:00:00	2025-11-04 20:00:00	3	ZnR0Z49d
1062	724	multiple-choice	<p>What structure may be injured at middle finger?</p>	f	100	2	2025-11-04 20:00:00	2025-11-04 20:00:00	3	kLRgmL9x
1063	725	multiple-choice	<p style="text-align: justify">A 50-year-old woman presented with a complaint of inability to bend her thumb, a condition she had experienced since a month prior to admission due to a traffic accident. She fall with outstretched hand. She had seen a bone setter three times but there was no improvement. Physical examination revealed a deformity in the MCP of her thumb, there is a hyperextension MCP joint, with limited movement due to pain. What is the possible pathology for this case?</p>	f	100	1	2025-11-04 20:02:00	2025-11-04 20:02:00	3	E4qe8W93
1065	726	multiple-choice	<p style="text-align: justify">A 12-year-old sustains a twisting injury to his ankle while playing soccer. His skin is intact and he has no evidence of neurovascular compromise. An injury radiograph is shown in Figure A. What is the next best step to optimize this patient’s outcome?</p>	f	100	1	2025-11-04 20:03:00	2025-11-04 20:03:00	3	vMqG4ZR7
1066	728	multiple-choice	<p>Assuming that the fracture shown in this radiograph (Figure 1) is aligned on the anteroposterior radiograph and heals in this position, secondary to fracture malalignment, the most likely loss of active motion will be</p>	f	100	1	2025-11-04 20:04:00	2025-11-04 20:04:00	3	j7Rw4eVG
1069	728	multiple-choice	<p>Assuming that the fracture shown in this radiograph (Figure 1) is aligned on the anteroposterior radiograph and heals in this position, secondary to fracture malalignment, the most likely loss of active motion will be</p>	f	100	2	2025-11-04 20:04:00	2025-11-04 20:04:00	3	52q3l6Re
1070	729	multiple-choice	<p style="text-align: justify">A 86-year-old woman presents with groin pain and mild limp after a fall. X-ray pelvis shows no obvious cortical disruption. She is treated as a soft-tissue injury but returns 5 days later with worsening pain. What is <strong>the </strong>most sensitive imaging for detecting occult femoral neck fracture?</p>	f	100	1	2025-11-04 20:05:00	2025-11-04 20:05:00	3	oER7m192
830	435	essay	<p>What is your diagnosis? (25)</p>	f	100	0	2018-12-02 10:16:04	2018-12-02 10:35:12	3	286d4da9cd8cd9f95fae7275046564f1
1071	730	multiple-choice	<p>An 84-year-old woman was brought to the emergency department after a low-energy fall at home. She complains of severe pain in her right hip and inability to bear weight. On examination, her right lower limb is shortened and externally rotated. Pelvic X-ray (AP view) shows a comminuted intertrochanteric fracture of the right femur with varus displacement and evidence of osteopenic bone<strong>.</strong></p><p></p><p>What is the major determinant of postoperative prognosis in elderly hip fracture patients?</p>	f	100	1	2025-11-04 20:06:00	2025-11-04 20:06:00	3	j8qavkRW
1072	731	multiple-choice	<p>An 80-year-old woman presents after a low-energy fall at home. She complains of severe right hip pain and inability to stand. Her leg is externally rotated and shortened. Pelvic X-ray shows a comminuted fracture line extending from the greater to lesser trochanter, with an intact femoral neck and subcapital region. Which of the following is a common mechanical complication after fixation in intertrochanteric fractures?</p>	f	100	1	2025-11-04 20:07:00	2025-11-04 20:07:00	3	8d9kzPV0
1073	731	multiple-choice	<p>An 80-year-old woman presents after a low-energy fall at home. She complains of severe right hip pain and inability to stand. Her leg is externally rotated and shortened. Pelvic X-ray shows a comminuted fracture line extending from the greater to lesser trochanter, with an intact femoral neck and subcapital region. Which of the following is a common mechanical complication after fixation in intertrochanteric fractures?</p>	f	100	1	2025-11-04 20:07:00	2025-11-04 20:07:00	3	p3V1vW9Z
1074	732	multiple-choice	<p style="text-align: justify">Which mechanism explains this?</p>	f	100	1	2025-11-04 20:08:00	2025-11-04 20:08:00	3	03VPJZqO
1075	732	multiple-choice	<p>What is the best management for this patient?</p>	f	100	2	2025-11-04 20:08:00	2025-11-04 20:08:00	3	5M9pGkV6
1076	733	multiple-choice	<p>A 66-year-old man presents with bilateral hand pain. The clinical examination and the radiograph shown in Figure 1 reveal diffuse osteoarthritis throughout the proximal interphalangeal (PIP) and distal interphalangeal (DIP) joints. His dominant index finger is the most symptomatic, and nonsurgical treatment, including bracing and cortisone injections, has failed. The patient elects PIP arthroplasty of the index finger. Compared with index arthrodesis, the expected outcome of index arthroplasty would be</p>	f	100	1	2025-11-04 20:10:00	2025-11-04 20:10:00	3	YMqxZMVL
1077	734	multiple-choice	<p style="text-align: justify">A 57-year-old male presents with worsening right ankle pain over the previous eight months. The patient has used an ankle gauntlet brace, received several corticosteroid injections, and taken scheduled NSAIDs, but his symptoms continue to worsen. Physical exam reveals limited ankle dorsiflexion and pain with plantar flexion that is limited to 20 degrees. There is no pain with ankle inversion or eversion. He does have a history of diabetes that is complicated by peripheral neuropathy. Current radiographs are depicted in figures A and B. What is the best treatment option for this patient?</p>	f	100	1	2025-11-04 20:59:00	2025-11-04 20:59:00	3	JyVAwZql
1078	735	multiple-choice	<p>A 68-year-old woman presents with chronic bilateral knee pain and progressive difficulty walking for 5 years. Pain worsens when descending stairs and is relieved by rest. On examination, there is varus deformity of the right knee, mild crepitus, and decreased range of motion. Anteroposterior (AP) X-rays of both knees (supine) show marked medial compartment joint-space narrowing on the right, moderate on the left, subchondral sclerosis, and marginal osteophytes, especially on the right. No joint effusion or periarticular erosion. Which of the following best explains the predominant medial compartment involvement in primary knee osteoarthritis?</p>	f	100	1	2025-11-04 20:11:00	2025-11-04 20:11:00	3	7X9EN4qG
1079	736	multiple-choice	<p>A 66-year-old man with advanced medial compartment OA presents with varus deformity and pain localized to medial joint line. Radiographs show intact lateral and patellofemoral compartments. What is the ideal surgical option for this patient</p>	f	100	1	2025-11-04 20:12:00	2025-11-04 20:12:00	3	ljqyvjRB
903	471	multiple-choice	<p>A 38-year-old man caught his index finger in a volleyball net. He noted an angular deformity of the finger that was reduced when a teammate pulled on his finger. Three weeks later, he now reports trouble extending his finger. A clinical photograph is shown below.</p>\n\n<p>What anatomic structure is most likely injured?</p>	t	100	0	2018-12-03 14:33:32	2018-12-03 14:33:32	3	c653e1a28b16069ed5355aa7c69e9e5d
904	472	multiple-choice	<p>A 23-year-old woman is involved in a motorcycle accident. She sustains bilateral femur fractures (Abbreviated Injury Score [AIS]=3), an intra-abdominal injury (AIS=3), facial fractures (AIS=2), and a pulmonary injury (AIS=2).</p>\n\n<p>What is her Injury Severity Score (ISS)?</p>	t	100	0	2018-12-03 14:34:53	2018-12-03 14:34:53	3	139a21a28c4314fd7615673f11e0ce4c
905	473	multiple-choice	<p>A 52-year-old woman who is right hand-dominant sustains an injury to her elbow in a fall. A radiograph is shown below.</p>\n\n<p>The preferred treatment of this injury pattern should include..?</p>	t	100	0	2018-12-03 14:37:12	2018-12-03 14:37:12	3	fd5d88723c8e02d57e8e088669da7815
906	474	multiple-choice	<p>A 29-year-old man complains of a painful left distal thigh and knee pain. Imaging reveals a lesion in the distal femoral metaphysis extending into the lateral femoral condyle. An incisional biopsy is planned for this mass in the distal femur.</p>\n\n<p>Which of the following is recommended regarding treatment of these lesions?</p>	t	100	0	2018-12-03 14:38:50	2018-12-03 14:38:50	3	11d9bc23834f445c47959c7b0dddae8e
907	475	multiple-choice	<p>A 22 year old male is admitted with fracture of the left femur. Two days later, he becomes midly confused, has a respiratory rate of 40 / min and scattered petechial rash on his upper torso. Chest x ray shows patchy alveolar opacities bilaterally. His arterial blood gas analysis is abnormal.</p>\n\n<p>The most likely diagnosis is..?</p>	t	100	0	2018-12-03 16:05:29	2018-12-03 16:05:29	3	94c4bbc14c03b42d91e5a0a3d3c681a2
908	476	multiple-choice	<p>Figure below shows the T2-weighted MR image through the L4-5 level of a 60-year-old man who has new-onset acute right lower-extremity pain and numbness and weakness in his right quadriceps muscle.</p>\n\n<p>The arrow in figure below is pointing to which structure?</p>	t	100	0	2018-12-03 16:07:49	2018-12-03 16:07:49	3	48038b8dd49fb1053fa486c080f18ffd
909	477	multiple-choice	<p>A 17-year-old girl is involved in a motor vehicle collision and sustains the injury shown in Figures below. She is neurologically intact in her bilateral lower extremities.</p>\n\n<p>Definitive treatment should consist of..?</p>	t	100	0	2018-12-03 16:10:40	2018-12-03 16:10:40	3	48acaa74aac0d36e35622ea465f9ed48
910	478	multiple-choice	<p>The major blood supply to the femoral head comes from which vessel?</p>	t	100	0	2018-12-03 16:17:07	2018-12-03 16:17:07	3	f2a90df7e322562cfdf44df79ca61a55
911	478	multiple-choice	<p>A formal multidisciplinary team approach to the co-management of geriatric patients with hip fracture has been shown to lead to..?</p>	t	100	0	2018-12-03 16:17:07	2018-12-03 16:17:07	3	d62d2dfdd52afd4dc701b391057a34b0
912	479	multiple-choice	<p>Which factor is a potential disadvantage of total hip arthroplasty compared to hemiarthroplasty for treatment of displaced femoral neck fracture in older patients with higher functional demands..?</p>	t	100	0	2018-12-03 16:18:47	2018-12-03 16:18:47	3	f80ac69367fc4aa2d8e7c55e3ac9c4b6
913	480	multiple-choice	<p>Which is the most common&nbsp; abnormality that related&nbsp; to this condition..?</p>	t	100	0	2018-12-03 16:29:57	2018-12-03 16:29:57	3	d1fa8ca9086375a5f79afe7811aeacd0
914	480	multiple-choice	<p>What is the stigmata for this disease?, <strong>except</strong></p>	t	100	0	2018-12-03 16:29:57	2018-12-03 16:29:57	3	93a5610bd88565301f264278c22378db
915	481	multiple-choice	<p>What type of muscle contraction occur while the muscle is lengthening?</p>	t	100	0	2018-12-03 16:31:23	2018-12-03 16:31:23	3	3ab81cf225a51df3a1396a20c0d825d0
916	482	multiple-choice	<p>What is the most appropriate treatment?</p>	t	100	0	2018-12-04 18:42:55	2018-12-04 18:42:55	3	f3838db20ca8b2167b34b558c93f28d4
917	482	multiple-choice	<p>The patient falls and undergoes imaging that demonstrates the lesion is unstable. What is the best next step?</p>	t	100	0	2018-12-04 18:42:55	2018-12-04 18:42:55	3	b27e0075516bda8703fe3abb04c91db2
918	482	multiple-choice	<p>The patient does well initially but returns for 4 months postsurgical evaluation with ongoing stiffness and pain despite going to physical therapy twice weekly and working on motion at home. She is unstable to bear weight comfortably.</p>\n\n<p>What is the best next step?</p>	t	100	0	2018-12-04 18:42:55	2018-12-04 18:42:55	3	4611dad1a9e0e19357ea399e893fbf6c
919	483	multiple-choice	<p>A 43-year-old man who has left shoulder pain with a traumatic rotator cuff tear after a fall. An examination reveals active forward elevation at 120 degrees and positive Yergason and lift-off test results. Arthroscopy reveals that the articular surfaces of the glenohumeral joint have a normal appearance without significant degenerative changes.</p>\n\n<p>What is the most appropriate treatment for this case?</p>	t	100	0	2018-12-04 18:46:01	2018-12-04 18:46:01	3	3dd7372dbce59c0ccbec1ebdfd5a0ca2
922	486	multiple-choice	<p>The x-ray below was the initial injury radiograph of 32 years-old man who sustained a closed injury to his right lower extremity after fall from a curb. Initial examination reveals a swollen painful ankle with pain both medially and laterally at the level of malleoli. Following surgical stabilization and fixation of distal fibula.</p>\n\n<p>What is the most appropriate next step?</p>	t	100	0	2018-12-04 18:56:46	2018-12-04 18:56:46	3	d42109457b7a3c58b19f2e2b53196dd6
923	487	multiple-choice	<p>The fracture fixation on this x ray delivers..?</p>	t	100	0	2018-12-04 19:03:59	2018-12-04 19:03:59	3	d274f343cc947f07530c58a5d97b0fee
924	487	multiple-choice	<p>What does it mean by an absolute stability</p>	t	100	0	2018-12-04 19:03:59	2018-12-04 19:03:59	3	dd745293c8e7d417bd245ac746b2ebb2
925	487	multiple-choice	<p>What does it mean by relative stability</p>	t	100	0	2018-12-04 19:03:59	2018-12-04 19:03:59	3	01aa36b257d7f296ee3b9f91ecb8126f
926	487	multiple-choice	<p>Radius ulna fractures as shown above require</p>	t	100	0	2018-12-04 19:03:59	2018-12-04 19:03:59	3	40190cbe0eeb9535b057c8151e215c75
927	488	multiple-choice	<p>The plate &nbsp;on Tibia as shown above plays a role as..?</p>	t	100	0	2018-12-06 15:58:37	2018-12-06 15:58:37	3	e345c714cef22aebadb7d25282c26e5f
928	488	multiple-choice	<p>The plate on Tibia as shown above provides ?</p>	t	100	0	2018-12-06 15:58:37	2018-12-06 15:58:37	3	ab1d2cc36443aa70f1a11c11cf49387a
929	489	multiple-choice	<p>Modulus Young describes...?</p>	t	100	0	2018-12-06 16:12:54	2018-12-06 16:12:54	3	8e933bf26b7fbeabcd714ce2546a9502
930	490	multiple-choice	<p>Data that support the diagnosis of such emergency is...?</p>	t	100	0	2018-12-06 16:19:05	2018-12-06 16:19:05	3	b072aff0cf3d92cb1d552700c7081ba9
931	490	multiple-choice	<p>Pathogenesis of the condition above as shown on the picture...?</p>	t	100	0	2018-12-06 16:19:05	2018-12-06 16:19:05	3	a004c1e799912efa8bf42b92ee89d9b5
932	490	multiple-choice	<p>Etiology that may cause such condition above as shown in the picture..?</p>	t	100	0	2018-12-06 16:19:05	2018-12-06 16:19:05	3	33432f5bd13e724a9c30b8a667786926
933	490	multiple-choice	<p>The patient eventually underwent fasciotomy. What is the important aspect of the post fasciotomy care of the patient..?</p>	t	100	0	2018-12-06 16:19:05	2018-12-06 16:19:05	3	e11e7e91e456fe5e1198dd696cccc0da
934	491	multiple-choice	<p>The component of Boutonierre deformity are...?</p>	t	100	0	2018-12-06 16:22:06	2018-12-06 16:22:06	3	8030e3b8959578873d095b02b94a8e94
935	492	multiple-choice	<p>The husband of a 22-year-old woman has hypophosphatemic rickets. The woman has no orthopaedic abnormalities, but she is concerned about her chances of having a child with the same disease.</p>\n\n<p>What should they be told regarding this disorder...?</p>	t	100	0	2018-12-06 16:23:59	2018-12-06 16:23:59	3	47a393745c1f54511a81b32072ed3ba3
936	493	multiple-choice	<p>Based on the diagnosis which of the following statement is more suitable...?</p>	f	100	0	2018-12-06 16:27:20	2018-12-06 16:28:52	3	9385865694e3d7f58c7a7923dedd2711
937	493	multiple-choice	<p>To attain proper&nbsp;culture and best residing bacteria that causes the infection it is best to take&nbsp;the specimen&nbsp;from..?</p>	f	100	0	2018-12-06 16:27:20	2018-12-06 16:28:52	3	d525ab3ea1da793889758d8bd02aa8d7
938	493	multiple-choice	<p>During the debridement, the orthopedic surgeon encounters large dead space resulted from the active tissue and bone infection. Which of the following is most suitable to choose to fill in the dead space..?</p>	t	100	0	2018-12-06 16:28:52	2018-12-06 16:28:52	3	5ccc429e919d188d75db74ceb79af3e8
939	494	multiple-choice	<p>What is the most appropriate next step in management...?</p>	t	100	0	2018-12-06 17:04:56	2018-12-06 17:04:56	3	fe4090f22720e94ffcf8a068438fa819
940	494	multiple-choice	<p>The patient is brought to the operating room and dishwater like fluid is drained from the wound. The fascial planes are easily separated with blunt palpation.</p>\n\n<p>Tissue cultures are likely to show what type of bacteria?</p>	t	100	0	2018-12-06 17:04:56	2018-12-06 17:04:56	3	3d1b6edd8877a30030500d33000949f8
941	494	multiple-choice	<p>Which of the following laboratory values is not associated with a diagnosis of soft tissue necrotizing infection?</p>	t	100	0	2018-12-06 17:04:56	2018-12-06 17:04:56	3	4ff60a04f0a5c5fcf696108cf09abae8
942	494	multiple-choice	<p>24 hours after the initial debridement, the patient has a dorsal hand wound measuring 5 &times; 4 cm with exposed tendon. His white blood count has decreased from 25,000/cc to 17,000/cc. His temperature is 38 degrees, heart rate is 88 bpm, and blood pressure is 100/64.</p>\n\n<p>What is the most appropriate next step in management...?</p>	t	100	0	2018-12-06 17:04:56	2018-12-06 17:04:56	3	db105d5f9b13f1353fa143abaf2b7c34
943	495	multiple-choice	<p>A 1-week-old infant was placed in a Pavlik harness for an Ortolani-positive hip. She was seen on a weekly basis and her hip remained dislocated 3 weeks later.</p>\n\n<p>What is the most appropriate treatment..?</p>	t	100	0	2018-12-06 17:06:35	2018-12-06 17:06:35	3	77a4ea93ab6edc006aac307ef9feb22c
944	496	multiple-choice	<p>What would be the most appropriate pre-surgery workup for a 14-year-old girl with a 65-degree scoliosis with caf&eacute; au lait cutaneous lesions and axillary freckling..?</p>	t	100	0	2018-12-06 17:09:00	2018-12-06 17:09:00	3	ec4fc180856e458619b8c6c3c2acda97
945	497	multiple-choice	<p>A 10-year-old girl who underwent surgical correction of a left clubfoot deformity at age one was brought to your clinic. The family is concerned that her left lower extremity is smaller than the right.</p>\n\n<p>What is the etiology of the size difference between the left and right...?</p>	t	100	0	2018-12-06 17:10:32	2018-12-06 17:10:32	3	d9d0095617e75890f968b0f3117076d3
946	498	multiple-choice	<p>What genetic defect is responsible for achondroplasia...?</p>	t	100	0	2018-12-06 17:11:55	2018-12-06 17:11:55	3	cc295d2f1c898a28c8c3e6c485659850
947	499	multiple-choice	<p>The optimal method to treat a recurrent presentation of pigmented villonodular synovitis (PVNS) with diffuse joint involvement in a 24-year-old woman with pain and symptomatic effusions is..?</p>	t	100	0	2018-12-11 09:17:12	2018-12-11 09:17:12	3	42fdbc8d6e91a4285040ec356f6fdc26
948	500	multiple-choice	<p>Ficat&rsquo;s classification for avascular necrosis of the femoral head based on...?</p>	t	100	0	2018-12-11 09:23:02	2018-12-11 09:23:02	3	b4c91663853380008d3d5235c72b1b4b
949	501	multiple-choice	<p>What is the mechanism of antimicrobial action of aminoglycoside antibiotics..?</p>	t	100	0	2018-12-11 09:24:39	2018-12-11 09:24:39	3	072b9b03360f2ae58b4a7a777d63c925
950	502	multiple-choice	<p>What is the primary function of 1,25-dihydroxyvitamin D..?</p>	t	100	0	2018-12-11 09:26:47	2018-12-11 09:26:47	3	0a1a5d9715e3ebf55d10277f00768224
951	503	multiple-choice	<p>Which of the following is considered the primary indication for surgery for patient with hallux valgus..?</p>	t	100	0	2018-12-11 09:33:38	2018-12-11 09:33:38	3	50c0979e060174013bff1054c3732f9f
990	521	essay	<p>Which sequence (T1 or T2) of MRI that show on the figure? (25)</p>	f	100	0	2018-12-17 02:41:51	2018-12-19 08:12:30	3	d2436dce5c74fa916eb5264ee3a2e117
991	521	essay	<p>Is the lesion superficial or deep to the fascia? (25)</p>	f	100	0	2018-12-17 02:41:51	2018-12-19 08:12:30	3	2e5d9168a8f54a11748d7cc88038d361
952	504	multiple-choice	<p>A 37-year-old man sustained a posterior hip dislocation with a femoral head fracture below the fovea (Pipkin I) as the result of fall off a roof. After undergoing closed reduction under general anesthesia, the hip is now stable in flexion ad abduction. Radiographs and a CT scan confirm anatomic reduction of the femoral head fragment and concentric reduction of the hip.</p>\n\n<p>Management should now include..?</p>	t	100	0	2018-12-11 09:53:46	2018-12-11 09:53:46	3	82c44d6b48efcf252568e7dd0c16955c
953	505	multiple-choice	<p>What are the most common site of septic arthritis in pediatrics patients..?</p>	t	100	0	2018-12-11 10:15:55	2018-12-11 10:15:55	3	f01532a90956f929c324954ced95265f
954	506	multiple-choice	<p>The result of total knee arthroplasties on osteoarthritis patients who are younger than age 55 are..?</p>	t	100	0	2018-12-11 10:17:43	2018-12-11 10:17:43	3	fd7a763a647bf53f9f092af16b7b188d
955	507	multiple-choice	<p>During stabilization of a slipped capital femoral epiphysis, the screw penetrates into the joint.&nbsp; The screw is repositioned so that it is within the femoral head.&nbsp;</p>\n\n<p>This transient penetration of the hip joint will most likely lead to..?</p>	t	100	0	2018-12-11 10:23:16	2018-12-11 10:23:16	3	7e805136c058048a5383ee9816fbc9e6
956	508	multiple-choice	<p>The following senteces about Rheumatoid arthritis is correct, <strong>except..?</strong></p>	t	100	0	2018-12-11 10:25:42	2018-12-11 10:25:42	3	bcdd8708b58bfa475836b9acd8b1908c
999	523	essay	<div>What structure is injured and what is the function? (30)</div>	f	100	0	2018-12-17 02:58:39	2018-12-17 02:58:39	3	010ad545dff6be4d55a9a1317704701f
957	509	multiple-choice	<p>A patient with severe rheumatoid arthritis reports progressive hip pain. Serial hip radiograps will most likely show which of the following findings...?</p>	t	100	0	2018-12-11 10:27:26	2018-12-11 10:27:26	3	c2cd275db8e34c1d801cc47569c2745d
958	510	multiple-choice	<p>Which of the following is commonest organism causing acute osteomyelitis and acute septic arthritis...?</p>	t	100	0	2018-12-11 10:29:01	2018-12-11 10:29:01	3	aa518141540d1f4a2c218e01ef0c8aaf
959	511	multiple-choice	<p>The most critical factor in the prevention of chronic infection following severe open tibia fracture..?</p>	t	100	0	2018-12-11 10:30:57	2018-12-11 10:30:57	3	142adedc1bdf37a075d953131721b79f
960	512	essay	<p>What is your working diagnosis and classification? (20)</p>	f	100	0	2018-12-12 20:56:57	2018-12-12 20:56:57	3	68d5eaa02a90259817b39999c3625f8c
961	512	essay	<p>List down in details treatment strategies mandatory for this condition..? (40)</p>	f	100	0	2018-12-12 20:56:57	2018-12-12 20:56:57	3	3b373824fdaa565cc81242350e366cd9
962	512	essay	<p>What would you do for the detached bone fragment, Please Explain..? (40)</p>	f	100	0	2018-12-12 20:56:57	2018-12-12 20:56:57	3	d43d1b3034cbb576fc3c0a03eef531cc
963	513	essay	<p>What is your working diagnosis and classification..? (20)</p>	f	100	0	2018-12-12 20:59:14	2018-12-12 20:59:14	3	7ce91e591a0b793eeecb3422997d3c4b
964	513	essay	<p>Please explain the pathogenesis of this condition..? (40)</p>	f	100	0	2018-12-12 20:59:14	2018-12-12 20:59:14	3	d4db31bbe22e473620561db7a31c0699
965	513	essay	<p>How do you manage this condition..? (40)</p>	f	100	0	2018-12-12 20:59:14	2018-12-12 20:59:14	3	96e2dd5b238c840440c803e34deff414
966	514	essay	<p>Based on the clinical data and picture, what is your working diagnosis and list down 2 possible differential diagnosis..? (30)</p>	f	100	0	2018-12-12 21:03:57	2018-12-12 21:03:57	3	59e36cffae38daff1b515ae0a6bef4b0
967	514	essay	<p>Please explain the synopsis (ringkasan) of Ponseti Protocol based on the CAVE deformity (Cavus, Adductus, Varus and Equinus) corelated with anatomic part of the foot (forefoot, midfoot and hindfoot)..? (40)</p>	f	100	0	2018-12-12 21:03:57	2018-12-12 21:03:57	3	9930f074bb982d35ab69deb472ef60be
968	514	essay	<p>Please explain the complete and clear indication for Achilles tendon lengthening..? (30)</p>	f	100	0	2018-12-12 21:03:57	2018-12-12 21:03:57	3	dd6c20e6b55db9c82aa49b37873597c4
969	515	essay	<p>What is your working diagnosis and differential diagnosis..? (30)</p>	f	100	0	2018-12-12 21:08:03	2018-12-12 21:08:03	3	f8115f59864d4578b37cea17a070058c
970	515	essay	<p>What is your diagnostic work up to establis the diagnosis..? (20)</p>	f	100	0	2018-12-12 21:08:03	2018-12-12 21:08:03	3	0d45db0285bf4e0f90723871cc896641
971	515	essay	<p>What is your plan of action when the diagnostic work up turns out that the condition is muscular in origin? (30)</p>	f	100	0	2018-12-12 21:08:03	2018-12-12 21:08:03	3	ea7f490034da9fa24be00359541fb4dc
972	515	essay	<p>If the deformity is not corrected, what will happen to the future..? (20)</p>	f	100	0	2018-12-12 21:08:03	2018-12-12 21:08:03	3	24ce79d468b0222297828f63d308cbfd
973	516	essay	<p>What is your working diagnosis and differential diagnosis. Please explain your working diagnosis and differential diagnosis based on clinical data and x ray above..? (40)</p>	f	100	0	2018-12-12 21:13:14	2018-12-12 21:13:14	3	f1e646dfe0fa64e9d3fc33108944fd2d
974	516	essay	<p>What is your diagnostic work up..? (30)</p>	f	100	0	2018-12-12 21:13:14	2018-12-12 21:13:14	3	9ab70a69bca0f301568c2467d24c57ad
975	516	essay	<p>What is your plan of action..? (30)</p>	f	100	0	2018-12-12 21:13:14	2018-12-12 21:13:14	3	6b73ea9939fd323bf01b5d81be7929a3
976	517	essay	<p>Please describe the x-ray findings, at least 6 points! (30)</p>	f	100	0	2018-12-17 01:58:28	2018-12-17 02:03:12	3	c0708e01b8fd67365c71bb7ff8395a61
977	517	essay	<p>Please describe the chest CT finding! (20)</p>	f	100	0	2018-12-17 01:58:28	2018-12-17 02:03:12	3	06af4fb0a55855c8ece4cc4a85ce45f3
978	517	essay	<p>Please describe the histopathology result! (25)</p>	f	100	0	2018-12-17 01:58:28	2018-12-17 02:03:12	3	8685d42b0abe135c46d74ccd7f42e87f
979	517	essay	<p>What is the complete diagnosis? (25)</p>	f	100	0	2018-12-17 01:58:28	2018-12-17 02:03:12	3	988ed65c973fbdf84db8b5c2d7e1bc4c
980	518	essay	<p>Based on history and x-ray what is the most possible diagnosis? (30)</p>	f	100	0	2018-12-17 02:07:59	2018-12-17 02:09:32	3	2833bb408b1d335d2f55040aea82e614
981	518	essay	<p>Please assess the lesion with Mirel&rsquo;s score! (40)</p>	f	100	0	2018-12-17 02:07:59	2018-12-17 02:09:32	3	ea6e9cb9e9fe0e9b83cc0df33b4dbc93
982	518	essay	<p>What is the recommendation of treatment? (30)</p>	f	100	0	2018-12-17 02:07:59	2018-12-17 02:09:32	3	fc99dd59e3bcf43ed9b6ebf9787bf898
983	519	essay	<p>Please describe the CT scan finding! (30)</p>	f	100	0	2018-12-17 02:18:18	2018-12-17 02:24:26	3	dd38486a10ccce2177c01f54f79607bd
984	519	essay	<p>Please describe the histopathology finding! (30)</p>	f	100	0	2018-12-17 02:18:18	2018-12-17 02:24:26	3	85ff214cee6aef26a3763c710cdcf61b
985	519	essay	<p>Please mention the treatment option! (40)</p>	f	100	0	2018-12-17 02:18:18	2018-12-17 02:24:26	3	3219ed242542808ee7e2d58cbcbb4569
986	520	essay	<p>The matrix in this lesion suggest which benign tumor? (25)</p>	f	100	0	2018-12-17 02:23:40	2018-12-17 02:23:40	3	685fddc904ed5d31cd119dfea410aa02
987	520	essay	<p>Named two clinical features at least that suggest malignant transformation! (25)</p>	f	100	0	2018-12-17 02:23:40	2018-12-17 02:23:40	3	7d68deefa90073393e99f9a60871d698
1000	523	essay	<p>You have decided to perform an operation. What graft options that can be used? (30)</p>	f	100	0	2018-12-17 02:58:39	2018-12-17 02:58:39	3	39247e35947faa5b6f2151f5b75a05b0
1001	523	essay	<p>Supposed that the patient came to you three days after the injury and insisted to perform the operation immediately, how would you explain to the patient about the complication that most likely to occur? (20)</p>	f	100	0	2018-12-17 02:58:39	2018-12-17 02:58:39	3	7749afa63384c33972d9a133b0b12cdd
1002	524	essay	<p>What condition is shown in the pictures? (20)</p>	f	100	0	2018-12-17 03:02:45	2018-12-17 03:02:45	3	7ab126131bc262d5fee7a48252b9d754
1003	524	essay	<div>Mention the risk factors are associated with this condition, at least three? (30)&nbsp;</div>	f	100	0	2018-12-17 03:02:45	2018-12-17 03:02:45	3	f928b92a3042a605d8efbbefe4880808
1004	524	essay	<p>Mention others bones are commonly affected at least three? (30)</p>	f	100	0	2018-12-17 03:02:46	2018-12-17 03:02:46	3	1395a4a5b0ff6cf546af562ce0d7dd4d
1005	524	essay	<p>How would you manage this condition? (20)</p>	f	100	0	2018-12-17 03:02:46	2018-12-17 03:02:46	3	b4b0baa64b2d938c4af089a92c5c4873
1006	525	essay	<p>What is the most likely clinical diagnosis? (25)</p>	f	100	0	2018-12-17 03:05:51	2018-12-17 03:05:51	3	37ecebd49c28d137123ee35da69ec15c
1007	525	essay	<div>What are the risk factors of this condition? (25)</div>\n\n<p>&nbsp;</p>	f	100	0	2018-12-17 03:05:51	2018-12-17 03:05:51	3	2ba0c83a872cd8d4c5ffeab741778361
1008	525	essay	<p>What other investigation would you need? (25)</p>	f	100	0	2018-12-17 03:05:51	2018-12-17 03:05:51	3	b61be656302657c1b6bc93b3d4cf7ea5
1009	525	essay	<p>How would you treat? (25)</p>	f	100	0	2018-12-17 03:05:51	2018-12-17 03:05:51	3	36e953aa89a6a0610540a36ac4818ea0
1010	526	essay	<div>Based on the initial post-operative radiograph, what do you think is the most likely cause of the biomechanical failure causing the fracture? (25)</div>	f	100	0	2018-12-17 03:11:56	2018-12-17 03:11:56	3	07ef17dc9c749cfdb82b4e9119d1202a
1011	526	essay	<p>Describe the current radiologic picture! (25)</p>	f	100	0	2018-12-17 03:11:56	2018-12-17 03:11:56	3	1cf07c2d7745e1ec44e0a0df494b6d32
1012	526	essay	<div>What is your complete diagnosis (with classification)? (25)</div>	f	100	0	2018-12-17 03:11:56	2018-12-17 03:11:56	3	71481b1671927b40c8f451a61deb3907
1013	526	essay	<p>How would you treat this patient? (25)</p>	f	100	0	2018-12-17 03:11:56	2018-12-17 03:11:56	3	46280efcc2b08f832d9e822babba9031
1014	527	multiple-choice	<p>A 62-year-old woman reports diffuse aches and pains of the hip and pelvis. She denies any significant trauma but does have a history of chronic anemia. The figure shows a radiograph of the pelvis and skull and report of serum protein electrophoresis.</p>	f	100	0	2018-12-18 00:54:08	2018-12-18 00:54:22	3	cd2a5dbe7fe9e7b89d3c85db255a9162
1015	528	multiple-choice	<p>A 19-year-old woman reports right knee pain and fullness. The pain is worse with activity but is also present at rest. Radiographs are shown in the figures above. What is the most likely diagnosis?</p>	t	100	0	2018-12-18 00:58:20	2018-12-18 00:58:20	3	df164278fa24bf6c1e42a16cea02e1a9
1016	529	multiple-choice	<p>Figures above show the radiographs of a left proximal femoral lesion noted serendipitously following minor trauma to the left hip. The patient has no thigh pain and is fully active without limitation. What is the most likely diagnosis of this bony lesion?</p>	t	100	0	2018-12-18 01:02:51	2018-12-18 01:11:29	3	4600ba9cd427a2baae275cf7d1165c0e
1017	530	multiple-choice	<p>Which of the following is not a radiographic indicator of possible distal radioulnar joint (DRUJ) instability?</p>	t	100	0	2019-05-21 23:15:33	2019-05-21 23:15:33	3	7665ab2f7401558e1e85325016143c5c
1018	530	multiple-choice	<p>What is the appropriate next step in management?</p>	t	100	0	2019-05-21 23:15:34	2019-05-21 23:15:34	3	9912999c6bc0a37ccb1ee275577aeec5
1019	531	multiple-choice	<p>What is the best indicator of end-organ perfusion in this patient?</p>	t	100	0	2019-05-26 08:31:42	2019-05-26 08:31:42	3	e807d19c5eb34ac2d3b3b509af2cd8f1
1020	531	multiple-choice	<p>What is the most appropriate management of his extremity injuries?</p>	t	100	0	2019-05-26 08:31:42	2019-05-26 08:31:42	3	759b5707410eaa370726261e75c66e75
1021	531	multiple-choice	<p>What complication has been shown to increase as the interval between external fixator conversion and internal fixation prolonged?</p>	t	100	0	2019-05-26 08:31:42	2019-05-26 08:31:42	3	d5dec6199005584f64f447357438b8d9
1022	532	multiple-choice	<p>The Volkman fragment typically observed in this injury retains connections to which of the following ligaments?</p>	f	100	0	2019-05-26 08:39:23	2019-05-26 08:40:10	3	4cd0e3a68827713f3b0ba8c8f5687832
1023	532	multiple-choice	<p>The most appropriate initial management of this injury is:</p>	f	100	0	2019-05-26 08:39:23	2019-05-26 08:40:10	3	84b53c4dea817bec2ee19a52659556be
1024	532	multiple-choice	<p>According to the elements of successful pilon fracture management set forth by Ru&euml;di and Allg&ouml;wer, which of the following accurately describes the <strong>CORRECT ORDER OF STEPS</strong> for fixation:</p>	f	100	0	2019-05-26 08:39:23	2019-05-26 08:40:10	3	3cb26d84befca6daa63ce99a977d5261
1025	533	multiple-choice	<p>Based on the description of the patient&rsquo;s knee, what is the most appropriate next step in the management of this injury?</p>	t	100	0	2019-05-26 08:45:32	2019-05-26 08:45:32	3	e9883c9db007ec8d9c589b9ac1b51c45
1026	533	multiple-choice	<p>Regarding the surgical tactic used to address bicondylar tibial plateau fractures, which of the following statements is <strong>TRUE</strong>?</p>	t	100	0	2019-05-26 08:45:32	2019-05-26 08:45:32	3	4d5815fbe99d96d1a0822e597d7f16bc
1027	533	multiple-choice	<p>When do most symptomatic thromboembolic events occur after total joint arthroplasty?</p>	t	100	0	2019-05-26 08:45:32	2019-05-26 08:45:32	3	88d4ac6e0df8d22fba3ecb6c4cc2e724
1028	534	multiple-choice	<p>A 70-year-old woman has knee osteoarthritis which no longer manageable nonsurgically. Radiographs reveal a 30-degree mechanical axis deformity. When using the measured resection technique during total knee arthroplasty (TKA), the best way to avoid femoral malrotation is to reference the:</p>	t	100	0	2019-05-26 08:47:37	2019-05-26 08:47:37	3	9d9be15193692894d6263300f6bf1d7e
1029	535	multiple-choice	<p>Which modality has the broadest application for the reduction of postsurgical transfusion?</p>	t	100	0	2019-05-26 08:49:30	2019-05-26 08:49:30	3	e860cbbf8ea68d0f8530706c8a904f0b
1030	536	multiple-choice	<p>A 45-year-old man has an infected post right TKA. He has had two prior revision surgeries after the primary procedure. On clinical examination, he has a draining sinus in the mid-portion of his surgical scar and a range of motion of 5&deg; to 85&deg;. During surgery, the femoral component is found to be grossly loose, but the tibial component is well fixed. What is the most appropriate extensile approach that would provide adequate exposure and aid in tibial component extraction?</p>	t	100	0	2019-05-26 08:53:37	2019-05-26 08:53:37	3	7b08395cd6afd681366956e7d3321aa6
1048	552	multiple-choice	<p>Surgical management of the fracture shown in the picture will have what outcome compared with nonsurgical management in a sling?</p>	t	100	0	2019-05-26 09:38:55	2019-05-26 09:38:55	3	2cae7307eba1ae2b95937126e3dce4d2
1031	537	multiple-choice	<p>A 47-year-old obese man with a body mass index of 42 comes into the office with left knee pain 1 year after undergoing an uncomplicated left medial unicompartmental knee arthroplasty (UKA). Radiographs show a loose tibial component in varus. What is the most appropriate next step for this patient?</p>	t	100	0	2019-05-26 08:55:06	2019-05-26 08:55:06	3	09c03f2a60f0afcba3ebedc74aef230a
1032	538	multiple-choice	<p>A surgeon prepares a medial gastrocnemius rotational flap to cover a medial proximal tibia defect at the time of revision knee replacement surgery. To optimize coverage, the surgeon must optimally mobilize which artery?</p>	t	100	0	2019-05-26 08:56:39	2019-05-26 08:58:00	3	e451aa8e631984483938330bf2cdf48a
1033	539	multiple-choice	<p>A healthy, active 72-year-old man trips and falls, landing on his left hip 10 weeks after an uncomplicated left primary uncemented total hip replacement. A radiograph taken 6 weeks after surgery and before the fall is shown in Figure 1. A radiograph taken after the fall is shown in Figure 2. He is unable to bear weight and is brought to the emergency department. Examination reveals a slightly shortened left lower extremity and some mild ecchymosis just distal to the left greater trochanteric region, but his skin is intact, without abrasions or lacerations. What is the most appropriate treatment?</p>	t	100	0	2019-05-26 08:59:57	2019-05-26 08:59:57	3	34528e3a9e81e87e51c930ad552ef08e
1034	540	multiple-choice	<p><strong>The most likely diagnosis is:</strong></p>	t	100	0	2019-05-26 09:06:01	2019-05-26 09:06:01	3	34cca3662806269dc1163b5dbce3ec60
1035	540	multiple-choice	<p>The patient tells you that while this does not affect her functional activity she finds it to be painful in the mornings. However, she is not interested in having any kind of invasive intervention. The choice of treatment that you could offer her at this point in time would include which of the following?</p>	t	100	0	2019-05-26 09:06:01	2019-05-26 09:06:01	3	02a149e22efabc21ad984acb6a5e8e5e
1036	540	multiple-choice	<p>The patient returns 3 months later and now has pronounced triggering with the patient being able to demonstrate full composite fist in the office but, when trying to open the fingers, the ring finger remains stuck in the bent position. It requires considerable effort to straighten it and is accompanied by severe pain. The most appropriate form of treatment at this point in time would be which of the following, <strong>EXCEPT?</strong></p>	t	100	0	2019-05-26 09:06:01	2019-05-26 09:06:01	3	88e0a5344ce3e2f2d6dd438c3d05d965
1037	541	multiple-choice	<p>What sign or symptom may occur with cubital tunnel syndrome that <strong>DOES NOT</strong> occur with Guyon neuropathy?</p>	t	100	0	2019-05-26 09:08:17	2019-05-26 09:08:17	3	f52d4fb387b838419fee5f8e1134f479
1038	542	multiple-choice	<p>A 25-year-old man has an isolated flexor digitorum profundus laceration just proximal to the distal interphalangeal (DIP) flexion crease of his ring finger. The tendon ends are trimmed, removing 10 mm from each end (secondary to fraying) and the tendon repaired. Four months later, he reports limited finger motion of the long, ring, and small fingers. He cannot fully extend his wrist and all joints of the 3 fingers simultaneously. He has full passive flexion but cannot actively close his fingers completely into a fist. What is the most likely cause?</p>	t	100	0	2019-05-26 09:10:43	2019-05-26 09:10:43	3	a55ba6119b4209431a0f574bd4994b40
1039	543	multiple-choice	<p>Figures 24A&nbsp;through 24C are the radiographs of a 55-year-old woman who underwent a volar plating of an extra-articular distal radius fracture 2 weeks ago. She is experiencing weakness with flexion of the interphalangeal (IP) thumb joint. IP joint flexion was normal before surgery. What is the best next step?</p>	t	100	0	2019-05-26 09:15:39	2019-05-26 09:15:39	3	a05d876f06960556bcd39386b1abe8e1
1040	544	multiple-choice	<p>A 44-year-old man sustains the injury shown in Figures 25A through 25C. What is the most appropriate treatment?</p>	t	100	0	2019-05-26 09:19:45	2019-05-26 09:19:45	3	d98f4bba125c5831544457bccd54f640
1041	545	multiple-choice	<p>A 65-year-old woman homemaker with a 1-year history of insidious onset left wrist pain. She has failed conservative treatment and desires surgery. Her medical history is complicated by a smoking history of 1.5 packs of cigarettes per day. At the time of surgery, her capitate articular surface is normal in appearance. The best procedure for her would be</p>	t	100	0	2019-05-26 09:23:20	2019-05-26 09:23:20	3	561d7e4c94cceb8448f3b1fa86a6b6ce
1042	546	multiple-choice	<p>A 65-year-old woman has severe pain and numbness in her hand. She notes frequent awakenings at nighttime and difficulty with fine tasks. She also has a history of cervical radiculopathy and notes intermittent pain in her upper arm and periscapular region. An examination reveals a positive Tinel sign over the mid-forearm and carpal tunnel. Electrodiagnostic testing shows a median nerve sensory distal latency of 3.8 ms (normal latency is 3.5 ms). Which intervention or test would best predict if carpal tunnel release would be successful in relieving this patient&#39;s symptoms?</p>	t	100	0	2019-05-26 09:25:26	2019-05-26 09:25:26	3	777309eb5dac68d3586f58152b93a792
1043	547	multiple-choice	<p>Figures 28A&nbsp;and 28B are the radiographs of an 18-year-old man who had surgery 6 months ago at an outside institution. He is being referred now because he has persistent pain. He is tender over the scaphoid at the snuffbox. What is the most appropriate next imaging step in his pain workup?</p>	t	100	0	2019-05-26 09:28:00	2019-05-26 09:28:00	3	db3331bc61de9babe0c5cf3ba5480cff
1044	548	multiple-choice	<p>Figure 29 is the radiograph of a 22-year-old man who underwent an open reduction and pinning of a perilunate dislocation 10 weeks ago. The hardware has been removed. What is the best next step?</p>	t	100	0	2019-05-26 09:30:50	2019-05-26 09:30:50	3	db53880126ca51e683564c997901c83f
1045	549	multiple-choice	<p>A 38-year-old man sustains a terrible triad injury consisting of an elbow dislocation, comminuted and displaced radial head fracture, and a type I coronoid fracture. Intraoperative findings after radial head replacement and lateral collateral ligament complex repair reveal persistent instability consisting of medial opening on valgus stress and posteromedial subluxation of the ulnohumeral and radiocapitellar joints. What is the best next step?</p>	t	100	0	2019-05-26 09:32:43	2019-05-26 09:32:43	3	65e2e0e5bdbeb56b5a64ad2b911d8288
1046	550	multiple-choice	<p>A 35-year-old man falls off of a roof and sustains an extra-articular supracondylar elbow fracture. He had normal sensation in all fingers after the injury and before undergoing surgery to repair the fracture. The ulnar nerve was not transposed but was inspected prior to wound closure. Ten days after surgery, the patient has numbness in his small finger and is unable to cross his fingers. His elbow range of motion is 40&deg; to 100&deg;. What is the next appropriate step in management?</p>	t	100	0	2019-05-26 09:34:12	2019-05-26 09:34:12	3	cf8d480f28bd9c8c02c84ea00497a274
1047	551	multiple-choice	<p>The radiograph and axial CT scan of a 56-year-old right-hand-dominant man who sustains a right shoulder injury following a fall from a roof. He is seen in the emergency department and placed into a sling. He denies any previous injury to the shoulder. His medical history is significant only for hypertension. His arm is neurovascularly intact, and his deltoid is functioning. What is the most appropriate surgical option at this point?</p>	t	100	0	2019-05-26 09:36:26	2019-05-26 09:36:26	3	d019c0e466fefe7b7fcb07383f320eb4
1049	553	multiple-choice	<p>Figures 34A&nbsp;through 34D&nbsp;are the radiographs of a 55-year-old healthy woman who fell down a flight of steps while sleepwalking. When the surgeon replaces the radial head, the elbow dislocates posteriorly at 60&deg; of flexion as it is brought out from full flexion. What is the best next step?</p>	t	100	0	2019-05-26 09:42:47	2019-05-26 09:42:47	3	b04597f34c7f83aadc74bfd670c936ff
1050	554	multiple-choice	<p>Figures 35A&nbsp;through 35C are the radiographs of a 45-year-old man following acute trauma.<br />\nWhich radiographic finding indicates the likely need for a radial head replacement?</p>	t	100	0	2019-05-26 09:45:36	2019-05-26 09:45:36	3	b9b69d6b082f8a7b4c7c2f4f4184678f
1051	555	multiple-choice	<p>Figures above are the radiographs of a 61-year-old woman with a left elbow injury after a fall onto her outstretched hand. She denies any previous injury to her elbow. She undergoes a closed reduction of her elbow in the emergency department. What is the most appropriate next step in definitive management?</p>	t	100	0	2019-05-26 09:49:55	2019-05-26 09:49:55	3	ee493deda91f7ce5ff597a02220514f2
1052	556	multiple-choice	<p>The next most appropriate step in the management of this patient is:</p>	t	100	0	2019-05-26 10:15:44	2019-05-26 10:15:44	3	a6838ad51609dfc96223593680a55854
1053	556	multiple-choice	<p>The patient represents to you in 9 months. He reports that surgery was successful in decreasing his leg pain and weakness until 2 months ago. He now reports recurrent low back pain and right leg pain, similar to his first episode. Physical examination demonstrates a slightly decreased sensation on the lateral foot but no motor deficits. He demonstrates a positive straight leg raise test. The patient reports no fevers or chills and has a well-healed incision. Images from a gadolinium-enhanced magnetic resonance imaging (MRI) of his lumbar spine are shown in Figures 38 A and B. What is the most likely cause of the patient&rsquo;s recurrent symptoms?</p>	t	100	0	2019-05-26 10:15:44	2019-05-26 10:15:44	3	ad2ca08c2bb80ebc1f07cc80d17fa116
1054	557	multiple-choice	<p>Of the following, which is the most appropriate diagnostic test to confirm the suspected diagnosis?</p>	t	100	0	2019-05-26 10:21:16	2019-05-26 10:21:16	3	469f9747c23a709c1a3cb0a171b0968a
1080	737	multiple-choice	<p>A 34-year-old woman who underwent release of her first dorsal compartment at the wrist for de Quervain's tenosynovitis 3 months ago continues to report radial-sided wrist pain and tenderness similar to what she had prior to surgery. Examination appears classic for de Quervain's with a positive Finkelstein's test and continued pain with palpation over the first dorsal compartment. What is the likely source of her continued pain?</p>	f	100	1	2025-11-04 20:13:00	2025-11-04 20:13:00	3	Bx9OmmRe
\.


--
-- Data for Name: register_data; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.register_data (id, taker_id, taker_code, delivery_id, group_id, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: role_user; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.role_user (id, role_id, user_id, granted, created_at, updated_at) FROM stdin;
2	1	1	t	\N	\N
3	2	40	t	2025-11-04 13:40:25	2025-11-04 13:40:25
4	2	4	t	2025-11-04 13:40:25	2025-11-04 13:40:25
5	2	5	t	2025-11-04 13:40:25	2025-11-04 13:40:25
6	2	22	t	2025-11-04 13:40:26	2025-11-04 13:40:26
7	2	36	t	2025-11-04 13:40:26	2025-11-04 13:40:26
8	2	34	t	2025-11-04 13:40:26	2025-11-04 13:40:26
9	2	25	t	2025-11-04 13:40:26	2025-11-04 13:40:26
10	2	50	t	\N	\N
11	2	52	t	\N	\N
12	2	53	t	\N	\N
13	2	54	t	\N	\N
14	2	55	t	\N	\N
15	2	56	t	\N	\N
16	2	57	t	\N	\N
17	2	58	t	\N	\N
18	2	59	t	\N	\N
19	2	60	t	\N	\N
20	2	61	t	\N	\N
21	2	62	t	\N	\N
22	2	63	t	\N	\N
23	2	64	t	\N	\N
24	2	65	t	\N	\N
25	2	66	t	\N	\N
26	2	67	t	\N	\N
27	2	68	t	\N	\N
28	1	3	t	\N	\N
\.


--
-- Data for Name: roles; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.roles (id, name, slug, description, parent_id, created_at, updated_at, is_system, permissions, client_id) FROM stdin;
1	Administrator	administrator	Super Administrator	\N	2017-07-11 20:52:59	2017-07-11 20:52:59	f	\N	\N
2	Scorer / Committee	scorer	Test Scorer and/or committee	\N	2017-07-11 20:52:59	2017-07-11 20:52:59	f	\N	\N
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
\.


--
-- Data for Name: takers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.takers (id, name, reg, email, password, is_verified, created_at, updated_at, client_id) FROM stdin;
1	sari	\N	sari@ionbec.com	\N	t	2025-11-04 14:01:32	2025-11-04 14:01:32	3
2	dr. Febry Prayugo	BE 051125 - 01	prayugo.corp@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
3	dr. Mohamad Almer Sahala	BE 051125 - 02	almer.hutapea@yahoo.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
4	dr. Arham Adnani	BE 051125 - 03	adnani.arham@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
5	dr. Hanif Fitriawan	BE 051125 - 04	hanif.awang@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
6	dr. Muhammad Dimas Arya Candra Permana	BE 051125 - 05	mdacp99@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
7	dr. Rahadiyan Rheza Dewanto	BE 051125 - 06	rahadiyanrheza@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
8	dr. Rizky Andrey Rarung	BE 051125 - 07	rizkyrarung@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
9	dr. Rizwandha Noviar Azmi	BE 051125 - 08	rizwandhanoviarazmi@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
10	dr. Tri Taufiqurachman Telaumbanua	BE 051125 - 09	taufiq.rachman18@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
11	dr. Arie Kurniawan	BE 051125 - 10	ariedr7@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
12	dr. Mahardika Frityatama	BE 051125 - 11	mahardikaf@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
13	dr. Mohammad Muzakkiyafi	BE 051125 - 12	muzakiyafi@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
14	dr. M. Qathar RF Tulandi	BE 051125 - 13	mqatharrefa@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
15	dr. Bernadeta Fuad Paramita Rahayu	BE 051125 - 14	bernadeta.fuad.p@mail.ugm.ac.id	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
16	dr. Fuad Dheni Musthofa	BE 051125 - 15	fuaddmusthofa@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
17	dr. Prisilla Desfiandi	BE 051125 - 16	pdesfiandi@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
18	dr. Sharfan Anzhari	BE 051125 - 17	sharfanzhr@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
19	dr. Shannen Karsten	BE 051125 - 18	shannen_karsten@yahoo.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
20	dr. Ricovially Davya Guci	BE 051125 - 19	davyarico@yahoo.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
21	dr. Tommy Mandagi	BE 051125 - 20	tomymandagi.n@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
22	dr. Yudha Satria	BE 051125 - 21	dr.yudhasatria@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
23	dr. Andryan Hanafi Bakri	BE 051125 - 22	andryanh07@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
24	dr. Faiz Alam Rasyid	BE 051125 - 23	faizalamrasyid@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
25	dr. William Putera Sukmajaya	BE 051125 - 24	william.psky@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
26	dr. Handi Suntama Effendy	BE 051125 - 25	hs_philos@hotmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
27	dr. Muhammad Randi Akbar	BE 051125 - 26	mrandiakbar@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
28	dr. Satria Putra Wicaksana	BE 051125 - 27	satriaputrawicaksana3@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
29	dr. Ghozi Natul Isral	BE 051125 - 28	isralghozinatul@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
30	dr. Riko Febrian Kunta Adjie	BE 051125 - 29	rikokuntaadjie@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
31	dr. Alsyahrin Manggala Putra Sarif	BE 051125 - 30	alsyahrinp@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
32	dr. Ardian Mario	BE 051125 - 31	brozzmario27@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
33	dr. Taufiq Akbar	BE 051125 - 32	tafq.akbar@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
34	dr. Adiet Wahyu Kristian	BE 051125 - 33	adietkristian@yahoo.co.id	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
35	dr. Anak Agung Ngurah Krisna Dwipayana	BE 051125 - 34	krisnadwipayanaa@yahoo.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
36	dr. Gede Aditya Krisna	BE 051125 - 35	aditkrisna19@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
37	dr. Ignatius Angga Rusdianto	BE 051125 - 36	angga.rusdianto2704@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
38	dr. I Made Surya Budikusuma	BE 051125 - 37	budikusuma1012@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
39	dr. Mikhail Kertajanottama Kushadiwijaya	BE 051125 - 38	janottamakerta@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
40	dr. Sonia Daniati	BE 051125 - 39	soniadaniati@gmail.com	$2y$10$cSQg7iSEJzf/r9f21GUhNOPaNj1UR/SR7A7BLkCfe3gMc/MsnhDRe	f	2025-11-04 15:04:07	2025-11-04 15:04:07	\N
384	usertest072	1072	usertest072@test.com	\N	f	2018-07-14 10:56:49	2018-07-14 10:56:49	3
174	Pramono ari wibowo	1	dr.pramono@gmail.com	\N	f	2017-07-31 07:41:32	2017-07-31 07:41:32	3
181	bee	1	basukimh@gmail.com	\N	f	2017-12-10 12:50:22	2017-12-10 12:50:22	3
193	dr. Muhammad Anggawiyatna	31	wiyatna@gmail.com	\N	f	2017-12-24 22:56:48	2018-07-12 09:38:41	3
194	dr. Agus Waryudi	33	waryudiagus79@gmail.com	\N	f	2017-12-24 22:57:31	2018-07-12 09:41:08	3
195	dr. Damiarta Simorangkir	32	damiartas@gmail.com	\N	f	2017-12-24 22:58:13	2018-07-12 09:41:22	3
196	dr. Doli Mauliate Sitompul	34	mauliate84@gmail.com	\N	f	2017-12-24 22:59:08	2018-07-12 09:41:37	3
197	dr. Dyah Purnaning	35	dyah.purnaning@gmail.com	\N	f	2017-12-24 22:59:50	2018-07-12 09:41:53	3
198	dr. Muhammad Triadi Wijaya	36	m.triadi@gmail.com	\N	f	2017-12-24 23:00:28	2018-07-12 09:42:22	3
199	dr. Muh. Trinugroho Fahrudhin	37	fahrudhin1986@gmail.com	\N	f	2017-12-24 23:01:11	2018-07-12 09:44:43	3
210	dr. Hantonius	38	dedogtor@gmail.com	\N	f	2017-12-24 23:09:57	2018-07-12 09:45:25	3
227	dr. Muhammad Bayuaji Meiarso	15	dr.bayuaji@gmail.com	\N	f	2017-12-24 23:25:26	2018-07-12 09:51:42	3
228	dr. Dhina Hafiz Sa'ban	16	dhinahafizsaban54@gmail.com	\N	f	2017-12-24 23:26:04	2018-07-12 09:51:59	3
229	dr. Bagus Nur Graha Wahyu Aji	17	dr.bnwahyuaji@gmail.com	\N	f	2017-12-24 23:26:42	2018-07-12 09:52:21	3
231	dr. Zen Firhan	14	zenfirhanshahab@gmail.com	\N	f	2017-12-24 23:27:55	2018-07-12 09:54:25	3
232	dr. Alifia Rifki Rimanda	18	alifia.rifki@gmail.com	\N	f	2017-12-24 23:28:29	2018-07-12 09:54:41	3
235	dr. Dimas Triaryo	41	dr.dimastriaryo@gmail.com	\N	f	2017-12-24 23:30:45	2018-07-12 09:55:44	3
242	dr. Zainarda	24	fzee80@gmail.com	\N	f	2017-12-24 23:35:13	2018-07-12 09:58:08	3
243	dr. I Gusti Ngurah Yudhi Setiawan	21	charm.youd@gmail.com	\N	f	2017-12-24 23:35:49	2018-07-12 09:56:43	3
244	dr. Kadek Yuris Wira Artha	22	yurisartha@gmail.com	\N	f	2017-12-24 23:36:27	2018-07-12 09:57:19	3
245	dr. I Gusti Kadek Satrio Adiwardhana	20	ryo.wardhana7@gmail.com	\N	f	2017-12-24 23:37:00	2018-07-12 09:57:35	3
246	dr. Putu Suehandika Caksana	23	tuandi182@gmail.com	\N	f	2017-12-24 23:37:37	2018-07-12 09:57:50	3
247	dr. Fredy Arianto	19	fredya96@gmail.com	\N	f	2017-12-24 23:38:09	2018-07-12 09:58:25	3
253	dr. Kukuh Dwiputra Hemugrahanto	8	kukuhdh@gmail.com	\N	f	2017-12-27 13:08:28	2018-07-12 09:59:23	3
255	Ika Benny Kartikasari	4	dr.ikafk04@gmail.com	\N	f	2017-12-27 13:10:32	2018-07-12 09:59:46	3
256	dr. OK Ilham A. Irsyam	6	ok.ilham@gmail.com	\N	f	2017-12-27 13:11:12	2018-07-12 10:01:07	3
257	dr. Yesa A. Suwandani	7	suwandani2@gmail.com	\N	f	2017-12-27 13:11:58	2018-07-12 10:01:28	3
258	dr. Jifaldiafrian Maharajadinda Sedar	5	jiforthobaya@gmail.com	\N	f	2017-12-27 13:12:30	2018-07-12 10:01:45	3
259	dr. Gladys Adipranoto	21519-UB-1	g.adipranoto@gmail.com	\N	f	2017-12-28 00:11:24	2019-05-21 05:27:49	3
260	dr. Alwi Rachman	UB-6	awimuhdhar@gmail.com	\N	f	2017-12-28 00:12:14	2017-12-28 00:12:14	3
261	dr. Andika Dwiputra Djaja	BE191218-23	andika_dwiputra@rocketmail.com	\N	f	2018-07-05 12:56:38	2018-12-17 03:32:14	3
262	dr. Herjuno Ardhi	BE191218-25	herjuno.ardhi@gmail.com	\N	f	2018-07-05 12:57:10	2018-12-17 03:32:43	3
263	dr. M. Faj'rin Armin F	BE191218-26	fajrin_armin@yahoo.com	\N	f	2018-07-06 00:29:28	2018-12-17 03:32:59	3
264	dr. Putri Amalia Isdianto	BE191218-28	putri.isdianto@gmail.com	\N	f	2018-07-06 00:29:56	2018-12-17 03:34:00	3
265	dr. Helmi Ismunandar	39	dr.helmiismunandar@gmail.com	\N	f	2018-07-06 00:32:49	2018-07-12 10:03:53	3
266	dr. Tody Pinandita	40	tody_pinandita@yahoo.com	\N	f	2018-07-06 00:33:26	2018-07-12 10:04:19	3
267	dr. David Simorangkir	BE191218-41	daviid.sim@gmail.com	\N	f	2018-07-06 00:34:30	2018-12-17 03:37:58	3
268	dr. Gregorius Batara Putra Setia Sutradi	kandidat UNPAD 1	sutardigreg@gmail.com	\N	f	2018-07-06 00:34:57	2019-11-05 05:49:31	3
269	dr. Fajar Yulianto Karna Rajasa	BE191218-43	ianfajar8@gmail.com	\N	f	2018-07-06 00:35:21	2018-12-17 03:38:30	3
270	dr. Iwan Hipsa Achmad	BE191218-27	onehipsa@yahoo.com	\N	f	2018-07-06 00:35:56	2018-12-17 03:33:18	3
271	dr. Anita Kurniawati	BE191218-29	kurniawatianita85@gmail.com	\N	f	2018-07-06 00:36:22	2018-12-17 03:34:11	3
272	dr. Adam Fajar	BE191218-31	adam_fajar@yahoo.com.sg	\N	f	2018-07-06 00:42:14	2018-12-17 03:34:47	3
273	dr. Asep Sulaeman	BE191218-33	asepsulaeman12@gmail.com	\N	f	2018-07-06 00:42:38	2018-12-17 03:35:13	3
274	dr. Fadlyansyah Farid	BE191218-35	silongfarid@gmail.com	\N	f	2018-07-06 00:43:18	2018-12-17 03:35:47	3
275	dr. Boby Harul Priono	BE191218-37	bobyharulpriono@gmail.com	\N	f	2018-07-06 00:43:41	2018-12-17 03:36:30	3
276	dr. Agung Malinda	21519-UNAIR-1	A@SDADA.COM	\N	f	2018-07-06 00:47:33	2019-05-21 04:47:30	3
277	dr. Citra Ahdi Prasetya	BE191218-4	CACA@GMA.COM	\N	f	2018-07-06 00:47:57	2018-12-17 03:24:57	3
278	dr. Angga Fiandana	BE191218-13	ADAS@ASA.COM	\N	f	2018-07-06 00:48:17	2018-12-17 03:28:42	3
279	dr. Hudaya Nikmatullah	BE191218-16	AMSDN@FJAAD.COM	\N	f	2018-07-06 00:48:35	2018-12-17 03:29:37	3
280	dr. Nia Irayati	9	niairayati@gmail.com	\N	f	2018-07-06 00:53:11	2018-07-12 10:04:52	3
281	dr. Aries Freddy Hutabarat	10	arieshutabarat@gmail.com	\N	f	2018-07-06 00:53:30	2018-07-12 10:05:17	3
282	dr. Michael Horeb	11	michael.horeb@gmail.com	\N	f	2018-07-06 00:53:47	2018-07-12 10:05:35	3
283	dr. Herbert Yurianto	12	hyurianto@yahoo.com	\N	f	2018-07-06 00:54:04	2018-07-12 10:05:51	3
284	dr. Zuwanda Then	13	amink_zwd@hotmail.com	\N	f	2018-07-06 00:54:22	2018-07-12 10:06:08	3
285	dr. M. Fadli	BE191218-3	fadlyspot@gmail.com	\N	f	2018-07-06 00:57:17	2018-12-17 03:24:10	3
286	dr. Khrisnanto Nugroho	BE191218-12	khrisnanto.nugroho@gmail.com	\N	f	2018-07-06 00:57:39	2018-12-17 03:28:22	3
287	dr. Bagus Jati Nugroho	BE191218-9	bagusjt@gmail.com	\N	f	2018-07-06 00:57:57	2018-12-17 03:27:22	3
288	dr. Aris Kurniawan	21519-UNS-1	aris.orthopedi@gmail.com	\N	f	2018-07-06 00:58:14	2019-05-21 05:11:42	3
289	dr. Ronald Iskandar	BE191218-42	dr.ronald.iskandar@gmail.com	\N	f	2018-07-06 00:59:19	2018-12-17 03:38:13	3
290	dr. St. Hengkie Marseno	BE191218-30	virus_dlx@yahoo.com	\N	f	2018-07-06 00:59:37	2018-12-17 03:34:32	3
291	dr. Andika Dwi Cahyo	BE191218-40	andika.dwicahyo@gmail.com	\N	f	2018-07-06 00:59:55	2018-12-17 03:37:46	3
292	dr. Asa Ibrahim Zainal	BE191218-38	asaibrahimza@gmail.com	\N	f	2018-07-06 01:00:20	2018-12-17 03:36:55	3
293	dr. Arie Nugroho	21519-UGM-6	arienugroho171086@gmail.com	\N	f	2018-07-06 01:00:36	2019-05-21 05:18:03	3
294	dr. Marda Ade	BE191218-36	marhoochi@gmail.com	\N	f	2018-07-06 01:00:54	2018-12-17 03:36:04	3
295	dr. Hendi Dwi Bharata	BE191218-32	hd_bharata@yahoo.co.id	\N	f	2018-07-06 01:01:11	2018-12-17 03:35:02	3
296	dr. Sudaryanto	BE191218-34	sudaryantoku06@gmail.com	\N	f	2018-07-06 01:01:28	2018-12-17 03:35:32	3
297	dr. Trimanto Wibowo	BE191218-5	trimartowibowo@gmail.com	\N	f	2018-07-06 01:06:29	2018-12-17 03:25:22	3
298	dr. I Komang Mahendra Laksana M	BE191218-15	mahendralaksana@ymail.com	\N	f	2018-07-06 01:06:45	2018-12-17 03:29:17	3
299	dr. I Made Tusan S.	BE191218-17	toozanck@gmail.com	\N	f	2018-07-06 01:07:07	2018-12-17 03:29:59	3
300	dr. I.B. Gede Arimbawa	BE191218-20	ibg.arimbawa@gmail.com	\N	f	2018-07-06 01:07:30	2018-12-17 03:31:10	3
301	dr. Rakhmad Aditya Hernawan	1	aditlea@gmail.com	\N	f	2018-07-06 01:08:34	2018-07-12 10:07:03	3
302	dr. Warih Anggoro Mustaqim	2	mustaqim.ot@gmail.com	\N	f	2018-07-06 01:08:55	2018-07-12 10:07:23	3
304	dr. Hidayat	BE191218-6	hidayatrizal85@gmail.com	\N	f	2018-07-06 01:58:45	2018-12-17 03:25:46	3
305	dr. Rusendi Hidayat	BE191218-18	drrusendi@gmail.com	\N	f	2018-07-06 01:59:18	2018-12-17 03:30:39	3
306	dr. Brian Dhananjaya	BE191218-22	briandhananjaya@gmail.com	\N	f	2018-07-06 01:59:41	2018-12-17 03:31:42	3
307	Reza Mahruzza Putra	25	mahruzza13@gmail.com	\N	f	2018-07-06 02:00:17	2018-07-12 10:08:03	3
308	M.Rangga Akbari Siregar	26	r9rangga@yahoo.com	\N	f	2018-07-06 02:00:39	2018-07-12 10:08:27	3
309	M Hidayat Siregar	27	siregardayat@yahoo.co.id	\N	f	2018-07-06 02:00:56	2018-07-12 10:08:43	3
310	dr. Ira Juliet Anestessia	BE191218-39	irajuliet.dr@gmail.com	\N	f	2018-07-06 02:01:14	2018-12-17 03:37:27	3
311	Rudi Hadinata	29	hadinata_rudi@yahoo.com	\N	f	2018-07-06 02:01:34	2018-07-12 10:09:30	3
312	dr. Melitta setyarani(salah nama)	UI-1	setyarani@gmail.com	\N	f	2018-07-12 01:24:05	2018-07-12 01:24:05	3
313	dr. Aiflia Rifki Rimanda	18	adasda@yahoo.com	\N	f	2018-07-12 10:15:27	2018-07-12 10:15:27	3
314	usertest001	1001	usertest001@test.com	\N	f	2018-07-14 10:18:16	2018-07-14 10:18:16	3
315	usertest002	1002	usertest002@test.com	\N	f	2018-07-14 10:18:41	2018-07-14 10:18:51	3
316	usertest003	1003	usertest003@test.com	\N	f	2018-07-14 10:19:14	2018-07-14 10:19:14	3
317	usertest004	1004	usertest004@test.com	\N	f	2018-07-14 10:19:35	2018-07-14 10:19:35	3
318	usertest005	1005	usertest005@test.com	\N	f	2018-07-14 10:20:08	2018-07-14 10:20:08	3
319	usertest006	1006	usertest006@test.com	\N	f	2018-07-14 10:20:32	2018-07-14 10:20:32	3
320	usertest007	1007	usertest007@test.com	\N	f	2018-07-14 10:20:51	2018-07-14 10:20:51	3
321	usertest008	1008	usertest008@gmail.com	\N	f	2018-07-14 10:21:16	2018-07-14 10:21:16	3
322	usertest009	1009	usertest009@test.com	\N	f	2018-07-14 10:21:38	2018-07-14 10:21:38	3
323	usertest010	1010	usertest010@test.com	\N	f	2018-07-14 10:21:56	2018-07-14 10:21:56	3
324	usertest011	1011	usertest011@test.com	\N	f	2018-07-14 10:22:18	2018-07-14 10:22:18	3
325	usertest012	1012	usertest012@test.com	\N	f	2018-07-14 10:22:37	2018-07-14 10:22:37	3
326	usertest013	1013	usertest013@test.com	\N	f	2018-07-14 10:23:21	2018-07-14 10:23:21	3
327	usertest014	1014	usertest014@test.com	\N	f	2018-07-14 10:23:45	2018-07-14 10:23:45	3
328	usertest015	1015	usertest015@test.com	\N	f	2018-07-14 10:24:09	2018-07-14 10:24:09	3
329	usertest016	1016	usertest016@test.com	\N	f	2018-07-14 10:24:34	2018-07-14 10:24:34	3
330	usertest017	1017	usertest017@test.com	\N	f	2018-07-14 10:25:01	2018-07-14 10:25:01	3
331	usertest018	1018	usertest018@test.com	\N	f	2018-07-14 10:25:21	2018-07-14 10:25:21	3
332	usertest019	1019	usertest019@test.com	\N	f	2018-07-14 10:25:38	2018-07-14 10:25:38	3
333	usertest020	1020	usertest020@test.com	\N	f	2018-07-14 10:26:44	2018-07-14 10:26:44	3
334	usertest021	1021	usertest021@test.com	\N	f	2018-07-14 10:27:03	2018-07-14 10:27:03	3
335	usertest022	1022	usertest022@test.com	\N	f	2018-07-14 10:27:21	2018-07-14 10:27:21	3
336	usertest023	1023	usertest023@test.com	\N	f	2018-07-14 10:27:40	2018-07-14 10:27:40	3
337	usertest024	1024	usertest024@test.com	\N	f	2018-07-14 10:27:58	2018-07-14 10:27:58	3
338	usertest025	1025	usertest025@test.com	\N	f	2018-07-14 10:28:22	2018-07-14 10:28:22	3
339	usertest026	1026	usertest026@test.com	\N	f	2018-07-14 10:28:42	2018-07-14 10:28:42	3
340	usertest027	1027	usertest027@test.com	\N	f	2018-07-14 10:28:59	2018-07-14 10:28:59	3
341	usertest028	1028	usertest028@test.com	\N	f	2018-07-14 10:29:24	2018-07-14 10:29:24	3
342	usertest029	1029	usertest029@test.com	\N	f	2018-07-14 10:29:47	2018-07-14 10:29:47	3
343	usertest030	1030	usertest030@test.com	\N	f	2018-07-14 10:30:08	2018-07-14 10:30:08	3
344	usertest031	1031	usertest031@test.com	\N	f	2018-07-14 10:30:24	2018-07-14 10:30:24	3
345	usertest032	1032	usertest032@test.com	\N	f	2018-07-14 10:30:40	2018-07-14 10:30:40	3
346	usertest033	1033	usertest033@test.com	\N	f	2018-07-14 10:31:00	2018-07-14 10:31:00	3
347	usertest034	1034	usertest034@test.com	\N	f	2018-07-14 10:31:24	2018-07-14 10:31:24	3
348	usertest036	1036	usertest036@test.com	\N	f	2018-07-14 10:32:04	2018-07-14 10:32:04	3
349	usertest037	1037	usertest037@test.com	\N	f	2018-07-14 10:42:49	2018-07-14 10:42:49	3
350	usertest038	1038	usertest038@test.com	\N	f	2018-07-14 10:43:08	2018-07-14 10:43:08	3
351	usertest039	1039	usertest039@test.com	\N	f	2018-07-14 10:43:24	2018-07-14 10:43:24	3
352	usertest040	1040	usertest040@test.com	\N	f	2018-07-14 10:43:40	2018-07-14 10:43:40	3
353	usertest041	1041	usertest041@test.com	\N	f	2018-07-14 10:43:58	2018-07-14 10:43:58	3
354	usertest042	1042	usertest042@test.com	\N	f	2018-07-14 10:44:24	2018-07-14 10:44:24	3
355	usertest043	1043	usertest043@test.com	\N	f	2018-07-14 10:44:41	2018-07-14 10:44:41	3
356	usertest044	1044	usertest044@test.com	\N	f	2018-07-14 10:44:58	2018-07-14 10:44:58	3
357	usertest045	1045	usertest045@test.com	\N	f	2018-07-14 10:45:19	2018-07-14 10:45:19	3
358	usertest046	1046	usertest046@test.com	\N	f	2018-07-14 10:45:37	2018-07-14 10:45:37	3
359	usertest047	1047	usertest047@test.com	\N	f	2018-07-14 10:45:55	2018-07-14 10:45:55	3
360	usertest048	1048	usertest048@test.com	\N	f	2018-07-14 10:46:12	2018-07-14 10:46:12	3
361	usertest049	1049	usertest049@test.com	\N	f	2018-07-14 10:46:31	2018-07-14 10:46:31	3
362	usertest050	1050	usertest050@test.com	\N	f	2018-07-14 10:46:48	2018-07-14 10:46:48	3
363	usertest051	1051	usertest051@test.com	\N	f	2018-07-14 10:47:05	2018-07-14 10:47:05	3
364	usertest052	1052	usertest052@test.com	\N	f	2018-07-14 10:48:46	2018-07-14 10:48:46	3
365	usertest053	1053	usertest053@test.com	\N	f	2018-07-14 10:49:04	2018-07-14 10:49:04	3
366	usertest054	1054	usertest054@test.com	\N	f	2018-07-14 10:49:24	2018-07-14 10:49:24	3
367	usertest055	1055	usertest055@test.com	\N	f	2018-07-14 10:50:48	2018-07-14 10:50:48	3
368	usertest056	1056	usertest056@test.com	\N	f	2018-07-14 10:52:00	2018-07-14 10:52:00	3
369	usertest057	1057	usertest057@test.com	\N	f	2018-07-14 10:52:16	2018-07-14 10:52:16	3
370	usertest058	1058	usertest058@test.com	\N	f	2018-07-14 10:52:32	2018-07-14 10:52:32	3
371	usertest059	1059	usertest059@test.com	\N	f	2018-07-14 10:52:49	2018-07-14 10:52:49	3
372	usertest060	1060	usertest060@test.com	\N	f	2018-07-14 10:53:06	2018-07-14 10:53:06	3
373	usertest061	1061	usertest061@test.com	\N	f	2018-07-14 10:53:24	2018-07-14 10:53:24	3
374	usertest062	1062	usertest062@test.com	\N	f	2018-07-14 10:53:44	2018-07-14 10:53:44	3
375	usertest063	1063	usertest063@test.com	\N	f	2018-07-14 10:54:03	2018-07-14 10:54:03	3
376	usertest064	1064	usertest064@test.com	\N	f	2018-07-14 10:54:20	2018-07-14 10:54:20	3
377	usertest065	1065	usertest065@test.com	\N	f	2018-07-14 10:54:38	2018-07-14 10:54:38	3
378	usertest066	1066	usertest066@test.com	\N	f	2018-07-14 10:54:56	2018-07-14 10:54:56	3
379	usertest067	1067	usertest067@test.com	\N	f	2018-07-14 10:55:15	2018-07-14 10:55:15	3
380	usertest068	1068	usertest068@test.com	\N	f	2018-07-14 10:55:44	2018-07-14 10:55:44	3
381	usertest069	1069	usertest069@test.com	\N	f	2018-07-14 10:56:01	2018-07-14 10:56:01	3
382	usertest070	1070	usertest070@test.com	\N	f	2018-07-14 10:56:16	2018-07-14 10:56:16	3
383	usertest071	1071	usertest071@test.com	\N	f	2018-07-14 10:56:33	2018-07-14 10:56:33	3
385	usertest073	1073	usertest073@test.com	\N	f	2018-07-14 10:57:10	2018-07-14 10:57:10	3
386	usertest074	1074	usertest074@test.com	\N	f	2018-07-14 10:57:31	2018-07-14 10:57:31	3
387	usertest075	1075	usertest075@test.com	\N	f	2018-07-14 10:57:49	2018-07-14 10:57:49	3
388	usertest076	1076	usertest076@test.com	\N	f	2018-07-14 10:58:05	2018-07-14 10:58:05	3
389	usertest077	1077	usertest077@test.com	\N	f	2018-07-14 10:58:33	2018-07-14 10:58:33	3
390	usertest078	1078	usertest078@test.com	\N	f	2018-07-14 10:58:49	2018-07-14 10:58:49	3
391	usertest079	1079	usertest079@test.com	\N	f	2018-07-14 10:59:07	2018-07-14 10:59:07	3
392	usertest080	1080	usertest080@test.com	\N	f	2018-07-14 10:59:23	2018-07-14 10:59:23	3
393	usertest081	1081	usertest081@test.com	\N	f	2018-07-14 10:59:40	2018-07-14 10:59:40	3
394	usertest082	1082	usertest082@test.com	\N	f	2018-07-14 10:59:56	2018-07-14 10:59:56	3
395	usertest083	1083	usertest083@test.com	\N	f	2018-07-14 11:00:11	2018-07-14 11:00:11	3
396	usertest084	1084	usertest084@test.com	\N	f	2018-07-14 11:00:27	2018-07-14 11:00:27	3
397	usertest085	1085	usertest085@test.com	\N	f	2018-07-14 11:00:58	2018-07-14 11:00:58	3
398	usertest086	1086	usertest086@test.com	\N	f	2018-07-14 11:01:45	2018-07-14 11:01:45	3
399	usertest087	1087	usertest087@test.com	\N	f	2018-07-14 11:02:03	2018-07-14 11:02:03	3
400	usertest088	1088	usertest088@test.com	\N	f	2018-07-14 11:02:21	2018-07-14 11:02:21	3
401	usertest089	1089	usertest089@test.com	\N	f	2018-07-14 11:02:39	2018-07-14 11:02:39	3
402	usertest090	1090	usertest090@test.com	\N	f	2018-07-14 11:02:58	2018-07-14 11:02:58	3
403	usertest091	1091	usertest091@test.com	\N	f	2018-07-14 11:03:19	2018-07-14 11:03:19	3
404	usertest092	1092	usertest092@test.com	\N	f	2018-07-14 11:03:36	2018-07-14 11:03:36	3
405	usertest093	1093	usertest093@test.com	\N	f	2018-07-14 11:03:53	2018-07-14 11:03:53	3
406	usertest094	1094	usertest094@test.com	\N	f	2018-07-14 11:04:14	2018-07-14 11:04:14	3
407	usertest095	1095	usertest095@test.com	\N	f	2018-07-14 11:04:33	2018-07-14 11:04:33	3
408	usertest096	1096	usertest096@test.com	\N	f	2018-07-14 11:04:57	2018-07-14 11:04:57	3
409	usertest097	1097	usertest097@test.com	\N	f	2018-07-14 11:05:17	2018-07-14 11:05:17	3
410	usertest098	1098	usertest098@test.com	\N	f	2018-07-14 11:06:11	2018-07-14 11:06:11	3
411	usertest099	1099	usertest099@test.com	\N	f	2018-07-14 11:06:32	2018-07-14 11:06:32	3
412	usertest100	1100	usertest100@test.com	\N	f	2018-07-14 11:06:50	2018-07-14 11:06:50	3
413	Ahmad Hannan Amrullah	BE191218-1	hannanortho@gmail.com	\N	f	2018-11-20 06:24:49	2018-12-17 03:22:56	3
414	Kadek Seta Prawira	21519-UNAIR-3	seta.prawira@gmail.com	\N	f	2018-11-20 06:25:33	2019-05-21 04:48:22	3
415	Farindra Ridhalhi	BE191218-7	farindra3@gmail.com	\N	f	2018-11-20 06:26:22	2018-12-17 03:26:15	3
416	Donny Permana	21519-UNAIR-2	dr.donnypermana@gmail.com	\N	f	2018-11-20 06:27:03	2019-05-21 04:48:02	3
417	Rizky Agung Satria	BE191218-10	rizkysatria.kik@gmail.com	\N	f	2018-11-20 06:27:45	2018-12-17 03:27:44	3
418	Erfan Nasrullah	BE191218-19	rfun_n@yahoo.com	\N	f	2018-11-20 06:28:18	2018-12-17 03:30:55	3
419	Febrian  Brahmana	BE191218-21	febrian_brahmana@yahoo.com	\N	f	2018-11-20 06:31:12	2018-12-17 03:31:25	3
420	Teddy HW	0	teddyhw@gmail.com	\N	f	2018-11-20 07:05:04	2018-11-20 07:05:04	3
422	dr. Aria Adhitya Suyatno	21519-UI-1	ariaadhitya14@gmail.com	\N	f	2018-12-10 22:09:53	2019-05-21 00:02:39	3
423	dr. Jephtah Furano Lumban Tobing	21519-UI-2	jephurano@yahoo.com	\N	f	2018-12-10 22:10:56	2019-05-21 00:03:05	3
424	dr. Ade Martinus	21519-UI-3	ade.martinus88@gmail.com	\N	f	2018-12-10 22:11:41	2019-05-21 00:03:28	3
425	dr. Ajiantoro	21519-UI-4	ajiantoro99@gmail.com	\N	f	2018-12-10 22:12:10	2019-05-21 00:04:01	3
426	dr. Akbar Rizki Beni Asdi	21519-UI-5	akbarrba@gmail.com	\N	f	2018-12-10 22:13:01	2019-05-21 00:04:37	3
427	dr. Adisa Yusuf Reksoprodjo	21519-UI-6	adisayr@gmail.com	\N	f	2018-12-10 22:13:46	2019-05-21 00:04:59	3
428	dr. Aldo Fransiskus Marsetio	21519-UI-7	aldofransiskus@yahoo.com	\N	f	2018-12-10 22:14:26	2019-05-21 00:05:26	3
429	dr. Auliya Akbar	21519-UI-8	abay.jabz@gmail.com	\N	f	2018-12-10 22:15:06	2019-05-21 00:05:55	3
430	dr. Jessica Fiolin	21519-UI-9	jessica_fiolin@yahoo.co.uk	\N	f	2018-12-10 22:15:52	2019-05-21 00:06:22	3
431	dr. M. Deryl Ivansyah	21519-UI-10	deryl2006@yahoo.com	\N	f	2018-12-10 22:16:33	2019-05-21 00:06:49	3
432	dr. M. Rizki Adhi Primaputra	21519-UI-11	riaqi.adhi@yahoo.com	\N	f	2018-12-10 22:17:20	2019-05-21 00:07:13	3
433	dr. Rizki Hidayat	21519-UI-12	rizkihidayat1987@gmail.com	\N	f	2018-12-10 22:18:06	2019-05-21 00:07:52	3
434	dr. Rizky Priambodo Wisnubaroto	21519-UI-13	rizky.wisnubarotio@gmail.com	\N	f	2018-12-10 22:18:50	2019-05-21 00:08:31	3
435	dr. Zecky Eko Triwahyudi	21519-UI-14	zecky_oke@yahoo.com	\N	f	2018-12-10 22:19:23	2019-05-21 00:09:34	3
436	dr. Deny Mory Aryawan	21519-UNAIR-4	orioctapaedi@gmail.com	\N	f	2018-12-10 22:21:11	2019-05-21 04:48:41	3
437	dr. Hizbillah Yazid	21519-UNAIR-5	hizbillahyazid@gmail.com	\N	f	2018-12-10 22:22:10	2019-05-21 05:52:53	3
438	dr. Januar Ari Subiantoro	16 - 12520	jans_dr@ymail.com	\N	f	2018-12-10 22:23:40	2020-05-07 07:26:22	3
439	dr. I Gusti Ngurah Dodo Muliawan Ranuh	21519-UNAIR-8	dodo.ranuh@gmail.com	\N	f	2018-12-10 22:24:30	2019-05-21 04:51:36	3
440	dr. Maruli Oloan Tua	21519-UNPAD-2	marulioloantua@gmail.com	\N	f	2018-12-10 22:26:18	2019-05-21 04:54:40	3
441	dr. Gamal Ramdiputra	21519-UNPAD-3	gamalramadi@gmail.com	\N	f	2018-12-10 22:26:53	2019-05-21 04:54:56	3
442	dr. Ahmad Rizan Hendrawan	BE191218-2	rizan.hendra@yahoo.com	\N	f	2018-12-10 22:27:45	2018-12-17 03:23:21	3
443	dr. Angga Anggriawan	BE191218-8	a_angga_ang@yahoo.co.id	\N	f	2018-12-10 22:28:27	2018-12-17 03:26:57	3
444	dr. Alfa Januar Krista	BE191218-11	alfajanuardr@ymail.com	\N	f	2018-12-10 22:29:01	2018-12-17 03:28:01	3
445	dr. Glen Purnomo	BE191218-14	glen.purnomo@yahoo.com	\N	f	2018-12-10 22:29:25	2018-12-17 03:28:59	3
446	dr. Agung	UNHAS-NC1	agungm2014@gmail.com	\N	f	2018-12-10 22:29:48	2018-12-10 22:29:48	3
447	dr. Fajar Baskoro Gardjito	21519-UNS-2	baskoro_laziale@yahoo.com	\N	f	2018-12-10 22:30:32	2019-05-21 05:12:00	3
448	dr. Arief Indra Perdana P.	04-181120	absolutelydarkknight@gmail.com	\N	f	2018-12-10 22:31:14	2020-11-18 01:25:49	3
449	dr. Adi Surya Dharma	UNS-NC4	dr_adisurya_dharma@yahoo.co.id	\N	f	2018-12-10 22:31:55	2018-12-10 22:31:55	3
450	dr. Aditya Fuad Robby	21519-UGM-1	dr.robby_triangga@yahoo.com	\N	f	2018-12-10 22:32:41	2019-05-21 05:16:16	3
451	dr. Doni Agustian	21519-UGM-2	doni.ortho@gmail.com	\N	f	2018-12-10 22:33:07	2019-05-21 05:16:35	3
452	dr. Widyo Wahyu Pratomo	UGM-NC4	kaisaramadhan@gmail.com	\N	f	2018-12-10 22:33:44	2018-12-10 22:33:44	3
453	dr. Paramitha Dyah Lesmana	21519-UGM-3	meetha_dyah@yahoo.com	\N	f	2018-12-10 22:34:27	2019-05-21 05:17:26	3
454	dr. Ary Putra Noor	21519-UGM-4	aryputranoor@gmail.com	\N	f	2018-12-10 22:35:00	2019-05-21 05:17:04	3
455	dr. Aditya Warman	21519-UGM-5	adit3mil@gmail.com	\N	f	2018-12-10 22:35:39	2019-05-21 05:17:47	3
456	dr. Made Wirabhawa	21519-UNUD-1	madewirabhawa@hotmail.com	\N	f	2018-12-10 22:36:14	2019-05-21 05:24:11	3
457	dr. Komang Septian Sandiwidayat	21519-UNUD-2	tian_encephalon@yahoo.com	\N	f	2018-12-10 22:36:48	2019-05-21 05:24:33	3
458	dr. Aakash	21519-UNUD-3	aakashchatani@gmail.com	\N	f	2018-12-10 22:37:19	2019-05-21 05:24:47	3
459	dr. Ery Satriawan	21519-UB-2	ery_triple8@yahoo.co.id	\N	f	2018-12-10 22:39:02	2019-05-21 05:28:07	3
460	dr. Inggra Vivayuna	UB-NC3	inggra1st@yahoo.com	\N	f	2018-12-10 22:39:31	2018-12-10 22:39:31	3
461	dr. Fiki Nurandani	UB-NC4	fhuangkey@yahoo.com	\N	f	2018-12-10 22:40:00	2018-12-10 22:40:00	3
462	Henry dominica	21519-UNAIR-7	adasda@gmail.com	\N	f	2018-12-11 08:48:58	2019-05-21 04:49:54	3
464	dr. Andra Hendriarto	kandidat UI 1	andre.hendriarto@yahoo.com	\N	f	2019-05-21 00:11:01	2019-11-05 05:45:08	3
465	dr. Suyenci limbong	21519-UNAIR--6	suyenci@gmail.com	\N	f	2019-05-21 04:51:14	2019-05-21 04:53:35	3
466	dr. Musa Arafah	kandidat UNAIR 4	musa@gmail.com	\N	f	2019-05-21 04:52:37	2019-11-05 06:34:38	3
467	dr. Bayu Antara hadi	kandidat UNAIR 1	bar@gmail.com	\N	f	2019-05-21 04:53:22	2019-11-05 06:31:00	3
468	dr. Liliek Yudhantoro	21519-UNPAD-4	l.yudhantoro@yahoo.com	\N	f	2019-05-21 04:55:43	2019-05-21 04:55:43	3
469	dr. Preodita Agradi	21519-UNPAD-5	preodita@GMAIL.COM	\N	f	2019-05-21 04:56:29	2019-05-21 04:56:29	3
470	dr. Diki Julkarnain	kandidat UNPAD 2	dikijulkarnain@gmail.com	\N	f	2019-05-21 04:57:21	2019-11-05 05:53:46	3
471	dr.  Taufan Herwindo Dewangga	kandidat UNPAD 3	taufanhdewangga@gmail.com	\N	f	2019-05-21 04:58:18	2019-11-05 05:54:46	3
472	dr. Rendy Cahya Soetanto	kandiadt UNPAD 4	rendy.cahya@yahoo.com	\N	f	2019-05-21 04:59:07	2019-11-05 05:55:15	3
473	dr. Ricky Wibowo	kandidat UNPAD 5	rickywibowo@yahoo.com	\N	f	2019-05-21 05:01:02	2019-11-05 05:55:49	3
474	dr. Venansius Henry Perdana Suryanta	kandidat UNPAD 6	venansiusherry@yahoo.com	\N	f	2019-05-21 05:02:01	2019-11-05 05:56:20	3
475	dr. Yoan Putrasos Arif	kandidat UNPAD 7	putrasosarif@gmail.com	\N	f	2019-05-21 05:02:44	2019-11-05 05:56:45	3
476	dr. Bangkit Primayudha	kandidat UNPAD 8	tts.bangkit@gmail.com	\N	f	2019-05-21 05:03:49	2019-11-05 05:57:21	3
477	dr. Abdurrahman	kandidat UNPAD 9	indobrotherland@yahoo.com	\N	f	2019-05-21 05:04:37	2019-11-05 05:57:52	3
478	dr. Farry	kandidat UNPAD 10	farry_doank@yahoo.com	\N	f	2019-05-21 05:05:25	2019-11-05 05:58:15	3
479	dr. Anak Agung Gede Putra Prameswara	21519-UNHAS-1	d_dive_right_in@yahoo.com	\N	f	2019-05-21 05:06:47	2019-05-21 05:06:47	3
480	dr. Edwin William Thioritz	21519-UNHAS-2	willam_906@yahoo.com	\N	f	2019-05-21 05:07:45	2019-05-21 05:07:45	3
481	dr. Ery Wildan	21519-UNHAS-3	erywildan@gmail.com	\N	f	2019-05-21 05:08:17	2019-05-21 05:08:17	3
482	dr. Handoko	21519-UNHAS-4	handoko.lau@gmail.com	\N	f	2019-05-21 05:08:59	2019-05-21 05:08:59	3
483	dr. Jansen	21519-UNHAS-5	lee.jansen.88@yahoo.com	\N	f	2019-05-21 05:09:38	2019-05-21 05:09:38	3
484	dr. Yohannes Toban Layuk Allo	21519-UNHAS-6	yohannestoban@gmail.com	\N	f	2019-05-21 05:10:21	2019-05-21 05:10:21	3
485	dr. Anggita Tri Yurisworo	kandidat UNS 1	anggiortho@gmail.com	\N	f	2019-05-21 05:12:46	2019-11-05 06:10:03	3
486	dr. R. Bagas Widhiarso	kandidat UNS	bagas.works@gmail.com	\N	f	2019-05-21 05:13:20	2019-11-05 06:11:30	3
487	dr. M. Riyadli	21519-UNS-5	adiriyadi27@gmail.com	\N	f	2019-05-21 05:14:00	2019-05-21 05:14:00	3
488	dr. Abdaud Rasyid Y	kandidat UNS 3	abdaudry@gmail.com	\N	f	2019-05-21 05:14:39	2019-11-05 06:11:06	3
490	dr. Muhammad Bayu W	kandidat UGM 1	bayu.ortho@gmail.com	\N	f	2019-05-21 05:19:21	2019-11-05 06:19:42	3
491	dr. Dananjaya Putramega	kandidat UGM 2	putramega.dananjaya@gmail.com	\N	f	2019-05-21 05:19:57	2019-11-05 06:27:02	3
492	dr. Irsan Kesuma	kandidat UGM 3	irsanahong98@gmail.com	\N	f	2019-05-21 05:20:43	2019-11-05 06:25:41	3
493	dr. Irissandya D A	kandidat UGM 4	dr.iris@yahoo.com	\N	f	2019-05-21 05:21:19	2019-11-05 06:24:57	3
494	dr. Aditya Akbar W	kandidat UGM 5	adityaakbarwicaksosno@gmail.com	\N	f	2019-05-21 05:21:59	2019-11-05 06:23:24	3
495	dr. David Yosua P H	kandidat UGM 6	david_yosua@yahoo.com	\N	f	2019-05-21 05:22:32	2019-11-05 06:23:48	3
497	dr. Komang Arie Trysna Andika	38 - 12520	arieandika888@gmail.com	\N	f	2019-05-21 05:25:44	2020-05-07 07:35:34	3
498	dr. I Gusti Bagus Indra Angganugraha Putra Juniantara	35 - 12520	anggadoc06@yahoo.com	\N	f	2019-05-21 05:26:36	2020-05-07 07:34:33	3
499	dr. Ida Bagus Aditya Wirakarna	21519-UNUD-6	wirakarna@gmail.com	\N	f	2019-05-21 05:27:14	2019-05-21 05:27:14	3
502	dr. Elfiah	kandidat UB 1	elfiahmarekar@yahoo.com	\N	f	2019-05-21 05:30:12	2019-11-05 06:54:04	3
503	dr. I Gede Made Oka Rahaditya	kandidat UB 2	oka.rahaditya@gmail.com	\N	f	2019-05-21 05:31:22	2019-11-05 06:54:30	3
504	dr. Hanindya Prasojo	kandidat UB 3	hanindyaprasojo@gmail.com	\N	f	2019-05-21 05:31:57	2019-11-05 06:54:57	3
505	dr. Arimurti Pratianto	kandidat UB 4	arimurti03@gmail.com	\N	f	2019-05-21 05:32:27	2019-11-05 06:55:18	3
506	dr. Jeff Loren	21519-USU-1	dr.jeffloren@gmail.com	\N	f	2019-05-21 05:33:27	2019-05-21 05:33:27	3
507	dr. Randy Susanto	kandidat USU 1	f32ixalexander@gmail.com	\N	f	2019-05-21 05:34:10	2019-11-05 06:57:26	3
508	dr. Elvan Trianda	kandidat USU 2	elvantirandasato@gmail.com	\N	f	2019-05-21 05:34:47	2019-11-05 06:57:47	3
509	dr. Alamsyah Faritz Siregar	21519-USU-4	alamsyahfs@gmail.com	\N	f	2019-05-21 05:35:16	2019-05-21 05:35:16	3
510	dr. Irsan Abubakar	21519-UI-16	dr.irsan_abubakar@yahoo.com	\N	f	2019-05-21 05:51:37	2019-05-21 05:51:37	3
511	Eko Setiawan	kandidat UI 2	echo_stwan@yahoo.com	\N	f	2019-11-05 05:42:39	2019-11-05 05:42:39	3
512	dr. Irsan Abubakar	kandidat UI 3	dr.irsan_abubajar@yahoo.com	\N	f	2019-11-05 05:44:42	2019-11-05 05:44:42	3
513	dr. Muhammad Alvin Shiddieqie pohan	kandidat UI 4	alvinshid.pohan@gmail.com	\N	f	2019-11-05 05:46:04	2019-11-05 05:46:04	3
514	dr. Toto Surya Efar	kandidat UI 5	totosuryoefar@gmail.com	\N	f	2019-11-05 05:46:48	2019-11-05 05:46:48	3
515	dr. Faisal Rahman	next kandidat UI 1	me.faisalrahman@gmail.com	\N	f	2019-11-05 05:47:39	2019-11-05 05:47:39	3
516	dr. Muhammad Reza Saputra	19 - 12520	rezasaputra_mhd@yahoo.co.id	\N	f	2019-11-05 05:48:27	2020-05-07 07:27:34	3
517	dr. Anggi Fauzan	kandidat UNPAD 11	giefauzan@hotmail.com	\N	f	2019-11-05 05:59:08	2019-11-05 05:59:08	3
518	dr. Juliando	kandidat UNPAD 12	dokter_juliando@yahoo.com	\N	f	2019-11-05 06:00:01	2019-11-05 06:00:01	3
519	dr. Muhammad Fatikh Nanda Perdana	kandidat UNPAD 13	nandaperdana@ymail.com	\N	f	2019-11-05 06:00:45	2019-11-05 06:00:45	3
520	dr. Gustman Lumanda Sitanggang	kandidat UNPAD 14	gusman200887@gmail.com	\N	f	2019-11-05 06:01:45	2019-11-05 06:01:45	3
521	dr. R. Moechammad Satrio Nugroho Magetsari	20 - 12520	magesari@gmail.com	\N	f	2019-11-05 06:02:57	2020-05-07 07:28:02	3
522	dr. Padlan Pasallo	kandidat UNHAS 1	padlanpasallo@ymail.com	\N	f	2019-11-05 06:04:41	2019-11-05 06:04:41	3
523	dr. Nur Rahmansyah	kandidat UNHAS 2	nurrahmansyah27@gmail.com	\N	f	2019-11-05 06:05:39	2019-11-05 06:05:39	3
524	dr. Andika Adiputra Thehumury	kandidat UNHAS 3	andikathehumury@gmail.com	\N	f	2019-11-05 06:06:32	2019-11-05 06:06:32	3
525	dr. Zulfan Zulkarnain	kandidat UNHAS 5	zulpanzulkarnain123@yahoo.com	\N	f	2019-11-05 06:07:30	2019-11-05 06:07:30	3
526	dr. Thomson	next kandidat UNHAS 1	manurungthomson@gmail.com	\N	f	2019-11-05 06:08:15	2019-11-05 06:08:15	3
527	dr. Michael Benjamin	39 - 12520	vicorgozaly84@gmail.com	\N	f	2019-11-05 06:09:20	2020-05-07 07:35:49	3
528	dr. Muhammad Riadli	kandidat UNS 5	adiriyadli27@gmail.com	\N	f	2019-11-05 06:12:33	2019-11-05 06:12:33	3
529	dr. Adhitya Indra Pradana	02 - 12520	adhityaindrapradhana@gmail.com	\N	f	2019-11-05 06:13:45	2020-05-07 07:15:56	3
530	dr. Dita Anggara Kusuma	12 - 12520	dokterdita8811@gmail.com	\N	f	2019-11-05 06:14:25	2020-05-07 07:21:10	3
531	dr. Misbahuddin	18 - 12520	dadakan16@gmail.com	\N	f	2019-11-05 06:15:04	2020-05-07 07:26:58	3
532	dr. Umar Kharisma Islami	28 - 12520	dr.aris_37@yahoo.com	\N	f	2019-11-05 06:18:36	2020-05-07 07:30:40	3
536	dr. Irene Araneta	14 - 12520	aranetairene@gmail.com	\N	f	2019-11-05 06:27:57	2020-05-07 07:25:35	3
537	dr. Rizky Admagusta	23 - 12520	rizkyadmagusta@yahoo.com	\N	f	2019-11-05 06:28:41	2020-05-07 07:29:09	3
538	dr. Andreas wahyu	05 - 12520	dr.andre07@gmail.com	\N	f	2019-11-05 06:29:24	2020-05-07 07:17:39	3
539	dr. Brilliant Citra Wirashada	kandidat UNAIR 2	brilwirashada@gmail.com	\N	f	2019-11-05 06:32:22	2019-11-05 06:32:22	3
540	dr. Dionysius Brampta Putra Manyakori	kandidat UNAIR 3	bramptapm@gmail.com	\N	f	2019-11-05 06:33:53	2019-11-05 06:33:53	3
541	dr. Andrianto P Perbowo	next kandidat UNAIR 2	ianperbowo@me.com	\N	f	2019-11-05 06:36:40	2019-11-05 06:36:40	3
542	dr. Stessy Benedicta	next kandidat UNAIR 3	stacy.benedicta@gmail.com	\N	f	2019-11-05 06:37:26	2019-11-05 06:37:26	3
543	dr. Goklas Ridwan R Gultom	next kandidat UNAIR 4	goklasgultom@gmail.com	\N	f	2019-11-05 06:38:09	2019-11-05 06:38:09	3
544	dr. Ronna Nuqtho H	next kandidat UNAIR 5	ronhidayatullah@gmail.com	\N	f	2019-11-05 06:38:59	2019-11-05 06:38:59	3
545	dr. Reyner Valiant Tumbelaka	22 - 12520	dr.reyner@gmail.com	\N	f	2019-11-05 06:40:31	2020-05-07 07:28:50	3
546	dr. Ansari Rahman	08 - 12520	ansarirahman86@gmail.com	\N	f	2019-11-05 06:41:10	2020-05-07 07:19:44	3
547	dr. hafidz Addatuang Ambong	next kandidat UNUD 3	hafidzambong82@gmail.com	\N	f	2019-11-05 06:44:06	2019-11-05 06:44:06	3
548	dr. Ni Made Puspa Dewi Astawa	40 - 12520	puspadewiastawa@gmail.com	\N	f	2019-11-05 06:45:42	2020-05-07 07:36:07	3
549	dr. Herryanto Agustriadi Simanjuntak	33 - 12520	herry_sjuntak@yahoo.com	\N	f	2019-11-05 06:46:31	2020-05-07 07:33:39	3
550	dr. Gusti Ngurah Putra Stanu	31 - 12520	putra.stanu@gmail.com	\N	f	2019-11-05 06:47:31	2020-05-07 07:32:54	3
551	dr. Stedy Adnyana Christian	next kandidat UNUD 7	saylovehoen@gmail.com	\N	f	2019-11-05 06:48:27	2019-11-05 06:48:27	3
552	dr. I G N Paramartha Wijaya P	next kandidat UNUD 8	paramarthawijaya123@gmail.com	\N	f	2019-11-05 06:49:12	2019-11-05 06:49:12	3
553	dr. Komang Indra Teguh Wisesa	26-270521	indrateguhwisessa@gmail.com	\N	f	2019-11-05 06:49:46	2021-05-27 07:23:50	3
554	dr. Ivander Purvance	37 - 12520	ivander_purvance@hotmail.com	\N	f	2019-11-05 06:51:17	2020-05-07 07:35:16	3
555	dr. I B Aditya Wirakrana	next kandidat UNUD 10	wirakrana@gmail.com	\N	f	2019-11-05 06:51:58	2019-11-05 06:51:58	3
556	dr. Soehartono Hadi Pranata	41 - 12520	Soe_hp@yahoo.com	\N	f	2019-11-05 06:52:44	2020-05-07 07:36:24	3
557	dr. Yudi Purbiantoro	30 - 12520	ud_fkub@yahoo.co.id	\N	f	2019-11-05 06:56:03	2020-05-07 07:31:17	3
558	dr. Fiski Purantoro	13 - 12520	fiski_p@yahoo.com	\N	f	2019-11-05 06:56:52	2020-05-07 07:21:37	3
559	dr. Alamansyah Faritz Siregar	kandidat USu 3	alamansyahfs@gmail.com	\N	f	2019-11-05 06:58:38	2019-11-05 06:58:38	3
561	dr. Viktor Gozaly	next kandidat UNHAS 2	victorgozaly84@gmail.com	\N	f	2019-11-05 07:02:28	2019-11-05 07:02:28	3
562	dr. Abdul Aziz	01 - 12520	aziz_orthomlg@gmail.com	\N	f	2019-11-05 08:01:34	2020-05-07 07:15:03	3
564	Ginanjar B. Prathama, dr., SpOT	1	ginanjarbudhip@gmail.com	\N	f	2020-04-02 22:04:18	2020-04-02 22:04:18	3
565	Hendrian Chaniago, dr., SpOT	2	hendrianchaniago@gmail.com	\N	f	2020-04-02 22:05:04	2020-04-02 22:05:04	3
566	Henry Tanzil, dr., SpOT	3	henrytanzil@ymail.com	\N	f	2020-04-02 22:05:46	2020-04-02 22:05:46	3
567	James Meinheart Pelealu, dr., SpOT	4	jpelealu25@gmail.com	\N	f	2020-04-02 22:06:25	2020-04-02 22:06:25	3
568	Roy Lisang, dr., SpOT	5	rlisang@yahoo.com	\N	f	2020-04-02 22:07:01	2020-04-02 22:07:01	3
569	Leonardus Hartoko, dr., SpOT	6	leonardush@yahoo.com	\N	f	2020-04-02 22:07:40	2020-04-02 22:07:40	3
570	Romy Darmawansa, dr., SpOT	7	romydarmawansa@yahoo.co.id	\N	f	2020-04-02 22:08:16	2020-04-02 22:08:16	3
571	Didyn Nuzul Ariefin, dr., SpOT	8	indrapura@gmail.com	\N	f	2020-04-02 22:08:53	2020-04-02 22:08:53	3
572	Setyagung Budi Santosa, dr., Sp.OT,CCD	9	setyagungbs@yahoo.com	\N	f	2020-04-02 22:09:26	2020-04-02 22:09:26	3
573	dr. Sammy Saleh Alhuraiby	25 - 12520	sammay.elharby@gmail.com	\N	f	2020-05-04 22:28:53	2020-05-07 07:29:52	3
574	dr. Ali Abdullah	03 - 12520	ali.abdullah@sungkar.net	\N	f	2020-05-04 22:29:50	2020-05-07 07:16:22	3
575	dr. Andi Praja Wira Yudha Luthfi	04 - 12520	wirapraja.med@gmail.com	\N	f	2020-05-04 22:30:29	2020-05-07 07:17:13	3
576	dr. Dina Aprilya	11 - 12520	dina.cia.aprilya@gmail.com	\N	f	2020-05-04 22:31:11	2020-05-07 07:20:52	3
577	dr. Ivan Mucharry Dalitan	15 - 12520	ivandalitan@gmail.com	\N	f	2020-05-04 22:31:45	2020-05-07 07:25:59	3
578	dr. Latsarizul Alfariq Senja Belantara	17 - 12520	latsarizulalfariq@gmail.com	\N	f	2020-05-04 22:32:25	2020-05-07 07:26:42	3
580	dr. Samuel Mauranaya	26 - 12520	samuelmauranaya@gmail.com	\N	f	2020-05-04 22:33:28	2020-05-07 07:30:06	3
582	dr. Ismail Salim	13-181120	ismailsalim1982@gmail.com	\N	f	2020-05-04 22:35:04	2020-11-18 01:29:22	3
583	dr. Felais Hediyanto Pradana	10-181120	deazfelais@gmail.com	\N	f	2020-05-04 22:35:42	2020-11-18 01:28:09	3
584	dr. Muhammad Ade Junaidi	15-181120	ade.junaidi@gmail.com	\N	f	2020-05-04 22:36:13	2020-11-18 01:30:03	3
585	dr. Prima Enky Merthana	16-181120	prima.enky@gmail.com	\N	f	2020-05-04 22:36:54	2020-11-18 01:30:28	3
586	dr. Yogi Ismail Gani	23-181120	yogiismailgani@yahoo.com	\N	f	2020-05-04 22:37:25	2020-11-18 01:32:46	3
589	dr. Arifin	09 - 12520	arifin85dr@gmail.com	\N	f	2020-05-04 22:39:41	2020-05-07 07:20:12	3
591	dr. Asyumeredha	10 - 12520	maredha.asriel@gmail.com	\N	f	2020-05-04 22:40:33	2020-05-07 07:20:33	3
592	dr. Ramadhan Anandita Putra	21 - 12520	ramadhan.putra88@yahoo.co.id	\N	f	2020-05-04 22:42:04	2020-05-07 07:28:26	3
595	dr. Steesy Benedicta	27 - 12520	stacy_benedicta@gmail.com	\N	f	2020-05-04 22:43:36	2020-05-07 07:30:25	3
597	dr. Raymond Parung	01-270521	raymond220983@gmail.com	\N	f	2020-05-04 22:44:59	2021-05-27 07:16:57	3
598	dr. R. Taufan Mulyo Wibisono	18-181120	rtmortho@gmail.com	\N	f	2020-05-04 22:45:27	2020-11-18 01:31:09	3
599	dr. Gana Adyaksa	11-181120	ganaadyaksa@gmail.com	\N	f	2020-05-04 22:45:57	2020-11-18 01:28:34	3
600	dr. Trixie Brevi Putri	21-181120	triziebreviputri@gmail.com	\N	f	2020-05-04 22:46:28	2020-11-18 01:31:59	3
601	dr. Adhinanda Gema Wahyudiputra	01-181120	nanorthobaya@gmail.com	\N	f	2020-05-04 22:46:58	2020-11-18 01:24:48	3
602	dr. R. Moechammad Satrio Nugroho Magetsari	13520-UNPAD1	satrio.magetsari@gmail.com	\N	f	2020-05-04 22:47:40	2020-05-04 22:47:40	3
603	dr. Aditya Priherdadi	06-270521	priherdadi@gmail.com	\N	f	2020-05-04 22:48:19	2021-05-27 07:18:20	3
604	dr. Priscilla	17-181120	reginapriscilla@ymail.com	\N	f	2020-05-04 22:48:48	2020-11-18 01:30:49	3
605	dr. Arnold David Pardamean	05-181120	arnold17845@gmail.com	\N	f	2020-05-04 22:49:18	2020-11-18 01:26:16	3
606	dr. Kemas Abdul Mutholib Luthfi	14-181120	luhtfinet@gmail.com	\N	f	2020-05-04 22:49:50	2020-11-18 01:29:42	3
607	dr. Cakra Andhika	07-270521	cakra1143@yahoo.com	\N	f	2020-05-04 22:50:21	2021-05-27 07:18:34	3
610	dr. Qariah Maulidiah	31-181120	maulidiahqariah@gmail.com	\N	f	2020-05-04 22:53:06	2020-11-18 01:35:05	3
611	dr. William Limoa	38-181120	william.limoa@gmail.com	\N	f	2020-05-04 22:53:34	2020-11-18 01:37:54	3
613	dr. Ricky Marasi Tambunan	33-181120	tambs_rq@yahoo.com	\N	f	2020-05-04 22:54:01	2020-11-18 01:35:44	3
614	dr. Pierre Alexander	29-181120	pierre.alexander.ortho@gmail.com	\N	f	2020-05-04 22:54:37	2020-11-18 01:34:36	3
615	dr. Stefan A.G.P. Kambey	35-181120	sciwora@yahoo.com	\N	f	2020-05-04 22:55:14	2020-11-18 01:36:32	3
616	dr. Randy Presly Octavianu	32--181120	randy.orthopediuh@gmail.com	\N	f	2020-05-04 22:55:50	2020-11-18 01:35:23	3
617	dr. Loli Anton	27-181120	lolianton87@gmail.com	\N	f	2020-05-04 22:56:27	2020-11-18 01:34:00	3
624	dr. Komang Indra Teguh Wisesa	UNUD 01	indrateguhwisesa@gmail.com	\N	f	2020-05-04 23:12:17	2021-05-05 05:02:11	3
625	dr. Putu Kermawan	30-181120	putukermawan@gmail.com	\N	f	2020-05-04 23:12:45	2020-11-18 01:34:50	3
626	dr. Dwiwahyonokusuma	25-181120	nonotjandra@gmail.com	\N	f	2020-05-04 23:13:39	2020-11-18 01:33:28	3
627	dr. Mario Daniel Simatupang	28-181120	mario_luztig@yahoo.com	\N	f	2020-05-04 23:14:09	2020-11-18 01:34:19	3
628	dr. Gede Agung Krisna Yudha	26-181120	krisnaagung112@gmail.com	\N	f	2020-05-04 23:14:55	2020-11-18 01:33:45	3
629	dr. Abdul Azis	13520-UB2	aziz.orthomlg@gmail.com	\N	f	2020-05-04 23:17:36	2020-05-04 23:17:36	3
630	dr. Fajar Sholehudin Salim	24-270521	fajar.salim89@gmail.com	\N	f	2020-05-04 23:18:07	2021-05-27 07:23:13	3
631	dr. Rizky Julana	20-181120	joereezky@gmail.com	\N	f	2020-05-04 23:18:32	2020-11-18 01:31:42	3
632	dr. Wongso Kusuma	22-181120	acheinsver86@gmail.com	\N	f	2020-05-04 23:19:02	2020-11-18 01:32:22	3
633	dr. Yohanes Augustinus	29 - 12520	grovio_26@yahoo.com	\N	f	2020-05-04 23:19:37	2020-05-07 07:31:00	3
634	dr. Andri Yandra Hidayat	06 - 12520	dr_adriyandra@yahoo.com	\N	f	2020-05-04 23:20:12	2020-05-07 07:18:04	3
635	dr. Agus Salim Lubis	02-181120	sugamilaslubis@gmail.com	\N	f	2020-05-04 23:20:43	2020-11-18 01:25:10	3
636	dr. Charles Apulta Meliala	08-181120	altameliala@gmail.com	\N	f	2020-05-04 23:21:19	2020-11-18 01:27:15	3
637	dr. Gean Juniwan Syahputra B	17-270521	ge_medico@yahoo.com	\N	f	2020-05-04 23:48:36	2021-05-27 07:21:34	3
638	dr. Yossie Atyanadhari	24-181120	yossie_blue@yahoo.com	\N	f	2020-05-04 23:49:27	2020-11-18 01:32:59	3
639	dr. Muhammad Afrizal Farkhan	19-270521	afrizalfarkhan@gmail.com	\N	f	2020-05-04 23:50:05	2021-05-27 07:22:03	3
640	dr. Reza Muttaqen	19-181120	orthoreza@gmail.com	\N	f	2020-05-04 23:50:38	2020-11-18 01:31:28	3
641	dr. Arya Maulana Nasution	06-181120	nasutionariya@gmail.com	\N	f	2020-05-05 00:09:12	2020-11-18 01:26:35	3
642	dr. Agus Wahyudi	03-181120	a_yoedi@yahoo.co.id	\N	f	2020-05-05 00:09:44	2020-11-18 01:25:28	3
643	dr. Bayu Sakti Jiwandono	07-181120	bayujiwandono@yahoo.co.id	\N	f	2020-05-05 00:10:16	2020-11-18 01:26:57	3
644	dr. Wijaya Johanes Chendra, Sp.OT	IHKS no.1	wijaya_j6@yahoo.com	\N	f	2020-10-15 10:01:59	2020-10-15 10:01:59	3
645	dr. Nico Raga, Sp.OT	IHKS no.2	nicoraga@gmail.com	\N	f	2020-10-15 10:02:47	2020-10-15 10:02:47	3
646	dr. Bunarwan Prihargono, SpOT	IHKS no.3	bunichiroo@gmail.com	\N	f	2020-10-15 10:03:31	2020-10-15 10:03:31	3
650	dr. Brian Vicky Faridyan, Sp.OT	IHKS no.6	brianfaridyan@gmail.com	\N	f	2020-10-15 10:05:33	2020-10-15 10:05:33	3
651	dr. Eka Mulyana, SpOT., FICS., M.Kes., SH., MHKes	IHKS no.7	ekamdspot@yahoo.com	\N	f	2020-10-15 10:06:09	2020-10-15 10:06:09	3
652	Yoppi ARI	IHKS TEST	yoppi.ari@gmail.com	\N	f	2020-10-16 13:32:46	2020-10-16 13:32:46	3
653	dr. Muhammad Ade Refdian Menkher	NC UI 01	adre.refdian@gmail.com	\N	f	2020-11-09 13:19:48	2020-11-09 13:19:48	3
654	dr. Fahmi Anshori	UI - 1	fhmanshori@yahoo.com	\N	f	2020-11-09 13:20:31	2022-05-17 14:37:28	3
655	dr. Elfikri Asril	NC UI 02	elfikri.asril@gmail.com	\N	f	2020-11-09 13:21:15	2021-05-05 04:16:21	3
656	dr. Naufal Ranadi Firas	02-270521	nau.orthobaya@gmail.com	\N	f	2020-11-09 13:27:53	2021-05-27 07:17:18	3
657	dr. Yuga Rahmadana	04-270521	yugarahmadana@gmail.com	\N	f	2020-11-09 13:38:52	2021-05-27 07:17:41	3
658	dr. Muhammad Pandu Nugrahadi	NC UNAIR 01	pandunugrahadi@gmail.com	\N	f	2020-11-09 13:39:41	2021-05-05 04:36:46	3
659	dr. Haris Dwi Khoirur Rofiq	03-270521	dr.harisdwi@gmail.com	\N	f	2020-11-09 13:40:25	2021-05-27 07:17:30	3
660	dr. I Putu Gede Pradnyadewa P	05-270521	iputugedepp@gmail.com	\N	f	2020-11-09 13:41:56	2021-05-27 07:18:05	3
661	dr. Denny Maulana	08-270521	dr.dennysiahaan@gmail.com	\N	f	2020-11-09 13:54:21	2021-05-27 07:18:51	3
662	dr. Hendy Rachmat Primana	11-270521	rphendy@gmail.com	\N	f	2020-11-10 05:04:01	2021-05-27 07:19:48	3
663	dr. Fary Tri Sabdillah	10-270521	ompalun@gmail.com	\N	f	2020-11-10 05:05:43	2021-05-27 07:19:30	3
664	dr. Doddy Putra Pratama Sudjana	09-270521	dr_doddyputra@yahoo.com	\N	f	2020-11-10 05:06:42	2021-05-27 07:19:13	3
665	dr. Firdaus Ramli	NC UNPAD 01	firdausramli0485@yahoo.com	\N	f	2020-11-10 05:07:29	2021-05-05 04:43:13	3
666	dr. Ramco Abtiza	12-270521	ramcoabtiza@yahoo.com	\N	f	2020-11-10 05:08:04	2021-05-27 07:20:03	3
667	dr. M. Eka Putra	NC UNPAD 02	m.eka1807@gmail.com	\N	f	2020-11-10 05:08:53	2021-05-05 04:43:33	3
668	dr. Naufal Chairulfatah	NC UNPAD 03	naufalism@gmail.com	\N	f	2020-11-10 05:09:35	2021-05-05 04:43:50	3
669	dr. Roichan Mochammad Firdaus	33-270521	firdaus.idoz232@gmail.com	\N	f	2020-11-10 05:14:08	2021-05-27 07:25:35	3
670	dr. Moh. Asri Abidin	35-270521	moh.asriabidin@gmail.com	\N	f	2020-11-10 05:14:42	2021-05-27 07:26:11	3
671	dr. Krishna Yudha	34-270521	nrp087@gmail.com	\N	f	2020-11-10 05:15:15	2021-05-27 07:25:56	3
672	dr. Marcell Wijaya	32-270521	marcell.wijaya.1986.2@gmail.com	\N	f	2020-11-10 05:15:56	2021-05-27 07:25:14	3
673	dr. Fajar Ivan Effendi	14-270521	fajareffendiortho@yahoo.com	\N	f	2020-11-10 05:18:14	2021-05-27 07:20:41	3
674	dr. Savero Iman Hari Suko	16-270521	saveroimanharisuko@gmsil.com	\N	f	2020-11-10 05:18:52	2021-05-27 07:21:11	3
675	dr. Hanif Andhika Wardhana	13-270521	hanifwardhana@gmail.com	\N	f	2020-11-10 05:19:36	2021-05-27 07:20:21	3
676	dr. Rosihan Effendi	15-270521	dreffe19@gmail.com	\N	f	2020-11-10 05:20:16	2021-05-27 07:20:58	3
677	dr. Okkie Mharga Sentana	NC UNS 02	okkie.ms90@gmail.com	\N	f	2020-11-10 05:20:52	2021-05-05 04:53:18	3
678	dr. Adhi Tanjung Laksono	NC UNS 01	tanjungortho@gmail.com	\N	f	2020-11-10 05:21:26	2021-05-05 04:53:03	3
679	dr. Rezky Winda Saraswaty	18-270521	saraswaty710@gmail.com	\N	f	2020-11-10 05:22:50	2021-05-27 07:21:45	3
682	dr. Dwijo Purboyo	27-270521	dwijo.purboyo@gmail.com	\N	f	2020-11-10 05:26:50	2021-05-27 07:24:02	3
683	dr. Gede Ketut Alit Satria Nugraha	28-270521	drgedekasn@gmail.com	\N	f	2020-11-10 05:27:34	2021-05-27 07:24:14	3
684	dr. Made Wahyu Dharmapradita	30-270521	g2a003111@gmail.com	\N	f	2020-11-10 05:28:24	2021-05-27 07:24:43	3
685	dr. Gde Dedy Andika	29-270521	dedyandika06@gmail.com	\N	f	2020-11-10 05:28:57	2021-05-27 07:24:30	3
686	dr. I Made Sumaria	31-270521	madesumaria86@gmail.com	\N	f	2020-11-10 05:29:30	2021-05-27 07:25:02	3
687	dr. Domy Pradana Putra	23-270521	domy_pradana_putra@yahoo.com	\N	f	2020-11-10 05:31:26	2021-05-27 07:22:59	3
688	dr. Tofan Margaret Dwi Saputra	25-270521	tofanmds29@gmail.com	\N	f	2020-11-10 05:32:04	2021-05-27 07:23:25	3
689	dr. Agustinus Budhi Prasetio	22-270521	sunitsugabudhi@gmail.com	\N	f	2020-11-10 05:32:46	2021-05-27 07:22:48	3
690	dr. Irfan Ritonga	21-270521	irfan_uhuy@yahoo.co.id	\N	f	2020-11-10 05:34:08	2021-05-27 07:22:34	3
691	dr. Fadli Yoga Arif	20-270521	fadli.arif@gmail.com	\N	f	2020-11-10 05:34:42	2021-05-27 07:22:15	3
692	dr. Muhammad Zulhandani	NC UI 03	dani.multafia@gmail.com	\N	f	2021-05-05 04:14:14	2021-05-05 04:16:57	3
693	dr. Richa Resmiati Musa	Adaptasi 02 - 2022	richamusa@yahoo.com	\N	f	2021-05-05 04:15:02	2022-04-04 12:37:21	3
694	dr. Uno Surgery Erwin	NC UI 05	unosurgery@yahoo.com	\N	f	2021-05-05 04:18:16	2021-05-05 04:18:16	3
695	dr. Taufik Akbar	NC UI 06	taufik.nazir.akbar@gmail.com	\N	f	2021-05-05 04:18:56	2021-05-05 04:18:56	3
696	dr. Ahmad Nugroho	NC UI 07	ahmad.nugroho18@gmail.com	\N	f	2021-05-05 04:19:42	2021-05-05 04:19:42	3
697	dr. Dody Kurniawan	NC UI 08	dodydocz@gmail.com	\N	f	2021-05-05 04:24:23	2021-05-05 04:24:23	3
699	dr. Husnul Verdian	NC UI 09	husnul.verdian@gmail.com	\N	f	2021-05-05 04:29:46	2021-05-05 04:29:46	3
700	dr. ido Prabowo	NC UI 10	idoprobowow@gmail.com	\N	f	2021-05-05 04:33:01	2021-05-05 04:33:01	3
701	dr. Windi Martika	UI NC 11	windi.martika@gmail.com	\N	f	2021-05-05 04:33:33	2021-05-05 04:33:33	3
702	dr. Fahmi Anshori	NC UI 12	fhmanshori@gmail.com	\N	f	2021-05-05 04:34:08	2021-05-05 04:34:08	3
703	dr. Arius Suwondo	NC UNAIR 02	arius.suwondo@gmail.com	\N	f	2021-05-05 04:37:41	2021-05-05 04:37:41	3
704	dr. Ferdiansyah Danang	NC UNAIR 03	ferdiansyahref@gmail.com	\N	f	2021-05-05 04:38:32	2021-05-05 04:38:32	3
705	dr. Cery Tarise Hajali	NC UNAIR 04	ceryhazali@gmail.com	\N	f	2021-05-05 04:39:05	2021-05-05 04:39:05	3
706	dr. Azmi Farhadi	NC UNAIR 05	azmifarhadidr@gmail.com	\N	f	2021-05-05 04:39:36	2021-05-05 04:39:36	3
707	dr. Muh. Abdurrahman Al Haraani	NC UNAIR 06	dokterarmand89@gmail.com	\N	f	2021-05-05 04:40:21	2021-05-05 04:40:21	3
708	dr. Mahyudin	NC UNPAD 04	mahyudin307@gmail.com	\N	f	2021-05-05 04:44:36	2021-05-05 04:44:36	3
709	dr. Novra Yuditya Santoso	NC UNHAS 01	yudit.santosa84@gmail.com	\N	f	2021-05-05 04:47:03	2021-05-05 04:47:03	3
710	dr. Sufandi Fahmi	NC UNHAS 02	sufandi_fahmi08@yahoo.com	\N	f	2021-05-05 04:47:44	2021-05-05 04:47:44	3
711	dr. Reza Romadhona Fahlevi	NC UNHAS 03	reza_r_fahlevi@yahoo.com	\N	f	2021-05-05 04:48:48	2021-05-05 04:48:48	3
712	dr. Vicky William Saranga Paundanan	NC UNHAS 04	williamvicky174@gmail.com	\N	f	2021-05-05 04:49:32	2021-05-05 04:49:32	3
713	dr. Iswahyudi	NC UNHAS 05	is_elfath@yahoo.co.id	\N	f	2021-05-05 04:50:03	2021-05-05 04:50:03	3
714	dr. Kerwin Halim	NC UNHAS 06	kerwin.halim@ymail.com	\N	f	2021-05-05 04:50:38	2021-05-05 04:50:38	3
715	dr. Yoshua Adi Nugroho	NC UNHAS 07	yan.wiryosuparto@gmail.com	\N	f	2021-05-05 04:51:22	2021-05-05 04:51:22	3
716	dr. Rahmad Rian	NC UNS 03	Rahmad.rian@gmail.com	\N	f	2021-05-05 04:54:23	2021-05-05 04:54:23	3
717	dr. Johan Dwi Murtanto	NC UNS 04	johanmurtanto@gmail.com	\N	f	2021-05-05 04:54:59	2021-05-05 04:54:59	3
718	dr. Bagus Iman Brilianto	NC UNS 05	bib.ortho@gmail.com	\N	f	2021-05-05 04:55:36	2021-05-05 04:55:36	3
719	dr. Denny Ardiansyah	NC UNS 06	adriansyahdenny@gmail.com	\N	f	2021-05-05 04:56:25	2021-05-05 04:56:25	3
720	dr. Fanny Indra Warman	NC UNS 07	fanny.warman@gmail.com	\N	f	2021-05-05 04:56:57	2021-05-05 04:56:57	3
721	dr. Zikrina Abyanti Lanodiyu	NC UGM 01	zikrina.lonudiyu158@gmail.com	\N	f	2021-05-05 04:59:55	2021-05-05 04:59:55	3
722	dr. Rosyad Nur Khadafi	NC UGM 02	khadafirosyadnur@gmail.com	\N	f	2021-05-05 05:00:35	2021-05-05 05:00:35	3
723	dr. Ahmad Ramdhoni Chusnanto	NC UGM 03	ramdhoni_ahmad@yahoo.com	\N	f	2021-05-05 05:01:09	2021-05-05 05:01:09	3
724	dr. Dea Prista Agatha	NC UGM 04	dea.prista@gmail.com	\N	f	2021-05-05 05:01:39	2021-05-05 05:01:39	3
725	dr. Indra Rukmana Tri Partistha	NC UNUD 01	dr_indra_pratisrtha@yahoo.com	\N	f	2021-05-05 05:05:20	2021-05-05 05:05:20	3
726	dr. Nico Lie	NC UNUD 02	nico.lie1611@gmail.com	\N	f	2021-05-05 05:06:00	2021-05-05 05:06:00	3
727	dr. Nyoman Gede BImantara	NC UNUD 03	oobbiimm@yahoo.com	\N	f	2021-05-05 05:06:35	2021-05-05 05:06:35	3
728	dr. Priza Razunif	NC UNUD 04	prizazunip.md@gmail.com	\N	f	2021-05-05 05:07:15	2021-05-05 05:07:15	3
729	dr. Dedde Aditya Rachman	NC UB 01	deddeaditya@gmail.com	\N	f	2021-05-05 05:09:40	2021-05-05 05:09:40	3
730	dr. Satria Wira Sakti	NC UB 0	satriawirasakti@yahoo.com	\N	f	2021-05-05 05:10:10	2021-05-05 05:10:10	3
731	dr. Hary Wahyu Agustono	NC UB 03	dr.ryuto@gmail.com	\N	f	2021-05-05 05:10:48	2021-05-05 05:10:48	3
732	dr. Ahmad Heifan	NC UB 04	heifan_ahmad@yahoo.com	\N	f	2021-05-05 05:11:25	2021-05-05 05:11:25	3
733	dr. Maulana Hasymi Hutabarat	NC UB 05	drmaulanahutabarat@gmail.com	\N	f	2021-05-05 05:12:09	2021-05-05 05:12:09	3
734	dr. Yovi Maulana	NC UB 06	yovimaulanahutabarat@gmail.com	\N	f	2021-05-05 05:12:47	2021-05-05 05:12:47	3
736	dr. Rachmad Gunawan	NC USU 01	rahmadgunawan88@gmail.com	\N	f	2021-05-05 05:14:37	2021-05-05 05:14:37	3
737	dr. Heru Hermantrie	NC USU 02	hermantriedr@yahoo.com	\N	f	2021-05-05 05:15:08	2021-05-05 05:15:08	3
738	dr. Budi Achmad Mulia Siregar	NC USU 03	budigen.siregar@yahoo.com	\N	f	2021-05-05 05:15:58	2021-05-05 05:41:22	3
739	dr. Samuel jason Rolando Tua Tobing	NC UNPAD 05	samuel3101jason@yahoo.com	\N	f	2021-05-05 06:03:13	2021-05-05 06:03:13	3
740	dr. Kusuma Rizky Anggi Sutrisno	NC UNPAD 06	kusumarizky89@gmail.com	\N	f	2021-05-05 06:04:02	2021-05-05 06:04:02	3
741	dr. R. M. David Jayanegara, Sp.OT	1	drbone81@gmail.com	\N	f	2021-07-01 06:52:24	2021-07-01 06:52:24	3
742	dr. M. Rizal Renaldi, M.Ked. Surg, Sp.OT	2	m.rizalrenaldi@gmail.com	\N	f	2021-07-01 06:58:00	2021-07-01 06:58:00	3
743	dr. Arif Wibowo, Sp.OT	3	arifwibowoicloud@gmail.com	\N	f	2021-07-01 06:58:22	2021-07-01 06:58:22	3
744	dr. Heppy Chandra Waskita, Sp.OT	4	dr.heppychandra@gmail.com	\N	f	2021-07-01 06:58:44	2021-07-01 06:58:44	3
745	dr. Paulus Ronald Hibono, Sp.OT	5	ronaldhibonomd@gmail.com	\N	f	2021-07-01 06:59:06	2021-07-01 06:59:06	3
746	dr. Kurniawan Silalahi, Sp.OT	6	karuniaspot@gmail.com	\N	f	2021-07-01 06:59:35	2021-07-01 06:59:35	3
747	dr. R. Rahendra Pratama, Sp.OT	7	hdrpratama@gmail.com	\N	f	2021-07-01 07:00:02	2021-07-01 07:00:02	3
748	dr. Berlianto Tjahjadi, Sp.OT	8	Tberlianto@yahoo.com	\N	f	2021-07-01 07:00:26	2021-07-01 07:00:26	3
749	dr. Reza Rahman Ramadhani, Sp.OT	9	rezarahmanramadhani@gmail.com	\N	f	2021-07-01 07:01:04	2021-07-01 07:01:04	3
750	dr. Ismail Bastomi, Sp.OT	10	ismailbastomi@gmail.com	\N	f	2021-07-01 07:01:29	2021-07-01 07:01:29	3
751	dr. Nyoman Orthi Laksana, Sp.OT	11	nyomanorthilaksana@gmail.com	\N	f	2021-07-01 07:01:50	2021-07-01 07:01:50	3
752	dr. Erick Pradykta	Adaptasi 01	dykta1618@gmail.com	\N	f	2021-10-26 22:09:33	2021-10-26 22:09:33	3
753	dr. Harris Kristanto	Adaptasi 01 -2022	harris1kristanto@gmail.com	\N	f	2021-10-26 22:10:10	2022-04-04 12:36:37	3
754	dr. Danar Lukman Akbar	UI-2	danarlukman@gmail.com	\N	f	2022-05-17 14:35:57	2022-05-17 14:35:57	3
755	dr. Didi Saputra Ramang	UI - 3	dsramang@gmail.com	\N	f	2022-05-17 14:38:18	2022-05-17 14:38:18	3
756	dr. Guntur Utama Putera	UI - 4	gunturutama17@gmail.com	\N	f	2022-05-17 14:39:14	2022-05-17 14:39:14	3
757	dr. Mohamnmad Walid Kuncoro	UI  - 5	walid.kuncoro@gmail.com	\N	f	2022-05-17 14:40:32	2022-05-17 14:40:32	3
758	dr. Petrus Aprianto	UI-6	petrus.aprianto.1989@gmail.com	\N	f	2022-05-17 14:41:37	2022-05-17 14:41:37	3
759	dr. Rizky Febriansyah Saleh	UI-7	riky.tujuh@gmail.com	\N	f	2022-05-17 14:42:11	2022-05-17 14:42:11	3
760	dr. Ronald Henry Tendean	UI-8	ronaldtendean143@yahoo.com	\N	f	2022-05-17 14:43:58	2022-05-17 14:43:58	3
761	dr. Ardiansyah	UI - 1A	ardi_harvard@yahoo.com	\N	f	2022-05-17 14:44:39	2022-05-17 14:44:39	3
762	dr. Aryo Winartomo	UI - 2A	aryowinartomo@hotmail.com	\N	f	2022-05-17 14:45:54	2022-05-17 14:45:54	3
763	dr. Erwin Ardian Noor	UI-3A	erwinardian@yahoo.com	\N	f	2022-05-17 14:46:30	2022-05-17 14:46:30	3
764	dr. I Wayan Arya Mahendra Karda	UI-4A	arya961mahendra@gmail.com	\N	f	2022-05-17 14:47:14	2022-05-17 14:47:14	3
766	dr. Muhammad Dedy Alkarni	UI-5A	dedyalkarni08@gmail.com	\N	f	2022-05-17 14:49:40	2022-05-17 14:49:40	3
767	dr. Riko Satriyo Wibowo	UI-6A	rikosw@gmail.com	\N	f	2022-05-17 14:50:24	2022-05-17 14:50:24	3
768	dr. Amanda Pratama	UNAIR-1	mndpratama@gmail.com	\N	f	2022-05-17 14:51:22	2022-05-17 14:51:22	3
771	dr. Caesar Haryo Bimoseno	UNAIR-4	caesarbimoseno@gmail.com	\N	f	2022-05-17 14:53:59	2022-05-17 14:53:59	3
772	dr. Jimmy Kuncoro	UNAIR-7	jimmykuncoro@yahoo.com	\N	f	2022-05-17 14:55:16	2022-05-17 14:55:16	3
774	dr. Tabita Prajasari	UNAIR-10	tabitaprajasari@gmail.com	\N	f	2022-05-17 15:02:34	2022-05-17 15:02:34	3
775	dr. Fachrizal Arfani Prawiragara	UNAIR-1A	arfanight@gmail.com	\N	f	2022-05-17 15:03:12	2022-05-17 15:03:12	3
776	dr. Yusuf Rizal	UNAIR-2A	uchuwp.rizal@gmail.com	\N	f	2022-05-17 15:03:50	2022-05-17 15:03:50	3
777	dr. Rizal Alexander Lisan	UNAIR-3A	rizalalexanderl@gmail.com	\N	f	2022-05-17 15:04:14	2022-05-17 15:04:14	3
778	dr. Muhamad Anggi Montazeri	UNPAD-3	anggi.zerio@gmail.com	\N	f	2022-05-17 15:05:41	2022-05-17 15:05:41	3
779	dr, Rifki Albana, MMR	UNPAD-4	rifkialbana@gmail.com	\N	f	2022-05-17 15:06:18	2022-05-17 15:06:18	3
780	dr. M Defri Saputra	UNPAD-1A	dr.defrisaputra@yahoo.com	\N	f	2022-05-17 15:07:02	2022-05-17 15:07:02	3
781	dr. David Rudianto Salim	UNPAD-2A	davidrsalim@yahoo.co.id	\N	f	2022-05-17 15:07:25	2022-05-17 15:07:25	3
782	dr. Afrisya Bimo Siwendro	UNPAD-3A	afrisyabimo.md@gmail.com	\N	f	2022-05-17 15:07:54	2022-05-17 15:07:54	3
783	dr. Astrawinata Guatama	UNHAS-1	astra.wg@gmail.com	\N	f	2022-05-17 15:08:29	2022-05-17 15:08:29	3
784	dr. Gerald Wonggokusuma	UNHAS-2	geraldwonggokusuma@gmail.com	\N	f	2022-05-17 15:09:05	2022-05-17 15:09:05	3
785	dr. Harry Supratama Azis	UNHAS-3	harryortopedi2018@gmail.com	\N	f	2022-05-17 15:09:30	2022-05-17 15:09:30	3
787	dr. Erich Svante Subagio	UNHAS-1A	erich.subagio@gmail.com	\N	f	2022-05-17 15:10:34	2022-05-17 15:10:34	3
788	dr. Gerry Dwi Putro	UNHYAS-2A	gerrydwiputro@yahoo.com	\N	f	2022-05-17 15:11:03	2022-05-17 15:11:03	3
789	dr. Indra Harianto Rante	UNHAS-3A	ranteindra@gmail.com	\N	f	2022-05-17 15:11:46	2022-05-17 15:11:46	3
790	dr. Maxmillian Alexandar Kawilarang	UNHAS-4A	maxmilliankawilarang@gmail.com	\N	f	2022-05-17 15:12:31	2022-05-17 15:12:31	3
791	dr. Mirza Ariandi	UNHAS_5A	mirzaariandi@gmail.com	\N	f	2022-05-17 15:13:15	2022-05-17 15:13:15	3
792	dr. Gilang Persada Aribowo	UNS-2	gil.orthosolo@gmail.com	\N	f	2022-05-17 15:14:15	2022-05-17 15:14:15	3
793	dr. Sigit Bayudono	UNS-3	sigitbayudono89@gmail.com	\N	f	2022-05-17 15:14:42	2022-05-17 15:14:42	3
794	dr. Muhammad Erstda Trapsilantya	UNS-1A	dr.traps@gmail.com	\N	f	2022-05-17 15:15:07	2022-05-17 15:15:07	3
795	dr. Ibnu Yudistiro	UNS-2A	iyudistiro@gmail.com	\N	f	2022-05-17 15:15:33	2022-05-17 15:15:33	3
796	dr. Alan Philips Kustianto Putra Resubun	UGM-1	alan.resubun@gmail.com	\N	f	2022-05-17 15:16:47	2022-05-17 15:16:47	3
797	dr. Andi Karsapin Tarsan	UGM-2	dr.andikarsapint@yahoo.com	\N	f	2022-05-17 15:17:19	2022-05-17 15:17:19	3
798	dr. Aristida Cahyono Putra	UGM-3	aristidacahyono@gmail.com	\N	f	2022-05-17 15:17:59	2022-05-17 15:17:59	3
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, avatar, name, username, email, email_verified_at, password, gender, profile_photo_path, birthplace, birthday, remember_token, last_login, created_at, updated_at, deleted_at, two_factor_secret, two_factor_recovery_codes, client_id, is_admin, admin_role) FROM stdin;
4	\N	Dr. dr. Muhammad Sakti, SpOT(K)	muhammad_sakti	muhammad_sakti@ionbec.com	\N	$2y$10$4RrdSgmIm2/G1TlbVd//5eDswwYu42WP32H.MpolU2rziv0hies/G	other	\N	\N	\N	\N	\N	2025-11-04 12:56:41	2025-11-04 13:43:58	\N	\N	\N	3	f	viewer
5	\N	Dr. dr. Mouli Edward, SpOT(K)	mouli_edward	mouli_edward@ionbec.com	\N	$2y$10$cVYcSk.Lx4GRCsnj.pKdfuyokFx9MGomTtlWscvrZU8S.y68bUqAC	other	\N	\N	\N	\N	\N	2025-11-04 12:56:42	2025-11-04 13:43:59	\N	\N	\N	3	f	viewer
2	\N	Pramono ari wibowo	Pramono	dr.pramono@gmail.com	\N	$2y$10$wVYIJfOslYFHZlIPX2BSou/FrUBzFOcMq5JhmUouUk/WfYHAzbsee	other	\N	\N	\N	3G9xTMyTIjbHoqy0BYu5jNpqbyfGwASQtClzQ43xASMFt4s0HbO732YiiXFt	2021-05-04 22:57:04	2017-07-11 22:22:21	2021-05-04 22:57:04	\N	\N	\N	3	f	viewer
1	\N	Administrator	admin	admin@localhost.com	\N	$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi	male	\N	\N	\N	WKNkoCoZ1USpDUx0Tv6qhOnS1t6FxDlY8ALe4Y8P8kYzbUGnZzpW6bvSbT7z	2022-09-16 16:26:56	2017-07-09 21:14:39	2025-11-04 12:31:36	\N	\N	\N	3	f	viewer
42	\N	Prof. Dr. Siki Kawiyana, dr. Sp.B, Sp.OT(K)	Siki Kawiyana	siki@gmail.com	\N	$2y$10$VigsdcwZ76Azp0uvMbl34.Ae7LVcg7jPtNP37KpQDO23ZFhtjfywa	other	\N	\N	\N	\N	2019-11-05 08:38:57	2019-11-05 08:35:55	2025-11-04 12:57:15	\N	\N	\N	3	f	viewer
40	\N	Istan Irmansyah, dr, Sp.OT(K)	irmansyah	istan_irmansyah@gmail.com	\N	$2y$10$9m3xsWFEhd4PjXU8XbEUw.vKWrUB5tLs38vUmVENgmJeO/VWEYiDy	other	\N	\N	\N	uTrY77LwauwYOLXqvyz8TiHmxErP3GXputrMeZrhXstROd2kP5SQUgJrF25b	2022-05-24 10:40:53	2019-05-29 10:32:09	2025-11-04 13:43:58	\N	\N	\N	3	f	viewer
36	\N	dr. Ihsan Oesman, SpOT(K)	ihsan	ihsan_oesman@yahoo.com	\N	$2y$10$QHx9Ks5Kb2diPE/q7FcasO0ZHPkXo1mzhUoOoW5M2yE10ZVjGnydW	other	\N	\N	\N	6E07ldETJ0Sp4OIez1SCrXspECO7naIWjEzdsMz87bsm93qiinixgxWeAury	2022-05-24 10:19:08	2018-07-10 16:04:11	2025-11-04 13:44:00	\N	\N	\N	3	f	viewer
37	\N	dr. Pranajaya Dharma Kadar, SpOT(K)	pranajaya	pranajayassg@yahoo.com	\N	$2y$10$Ed.ion5uwgTuHl09YNpYvOwUThQEvuxIw/0.B5tw5xgFkyX2zPnHu	other	\N	\N	\N	K9Zb7J4HkarVRr7ChsHwVRS9egjcPoRDtFBkJwLSPqV4cjeVhk2EpBm8h5t6	2020-05-09 15:12:15	2018-07-10 16:05:08	2025-11-04 13:44:01	\N	\N	\N	3	f	viewer
48	\N	dr. Krisna Yuarno, Sp. OT(K)	krisnayuarno	krisnayuarno@gmsil.com	\N	$2y$10$Hj9eABHjuE8eGCwengr36.jUPxJ2AW8lRv5IuAsNIfSTkyg1lpBfK	other	\N	\N	\N	\N	\N	2020-05-05 09:30:42	2025-11-04 13:44:01	\N	\N	\N	3	f	viewer
43	\N	Dr. Rendra Leonas, dr, Sp.OT(K)	rendra	rendra@gmail.com	\N	$2y$10$0WqdVDVealTbcWUsrB6v3uk7n1m673Yx42AVGydBX5gGr3LA/NpYC	other	\N	\N	\N	IhB2LgzPkArXeniRUD3vxnMB8O9QAQBg55ufA8zBBahpvBEiJJ2vu7vxwr5n	2021-05-27 09:00:20	2019-11-13 10:52:57	2025-11-04 13:44:02	\N	\N	\N	3	f	viewer
50	\N	Prof. Dr. Dwikora Novembri Utomo SpOT(K)	dnu	dwikora_utomo@yahoo.com	\N	$2y$10$O7GZtSwodlr0UvZ.yXWtaOe5WR0wBnURf2likv1jR7ugiP44rJti6	other	\N	\N	\N	Or4Cq5lXLhvc0AYioJ9Z2kye3UrVaNxWoqNp3R30C4UVNVnESepXQHJF3J7o	2022-05-24 10:41:26	2020-11-17 20:50:38	2025-11-04 13:46:00	\N	\N	\N	3	f	viewer
38	\N	dr. Aga Shahri Putera Ketaren, SpOT(K)	aga	aga_spk@yahoo.com	\N	$2y$10$BcdmPAvlQ5L7hb2oLIEtiOS1fJimJrKrCkO8oyZt54rBMW.XebKJe	other	\N	\N	\N	\N	2021-05-27 08:57:49	2018-07-10 16:07:58	2021-05-27 08:57:49	\N	\N	\N	3	f	viewer
39	\N	dr. Ifran saleh, Sp.OT(K)	ifran	ifransaleh@gmail.com	\N	$2y$10$hXEmn4kUUazjj6adunDTA.Cixj5kI5DxAP3RieWcLy4Ps.cc.ca.2	other	\N	\N	\N	ayTCPVsL4XQgdDpaWViGzUQhPZz6rOdEn2iqs9CMV38B37rvXfvDuMwVNl3Q	2019-11-13 10:51:26	2018-07-18 10:56:43	2019-11-13 10:51:26	\N	\N	\N	3	f	viewer
41	\N	dr. Tito Sumarwoto, Sp.OT(K)	tito	tito@gmail.com	\N	$2y$10$H7hQd8B2lL/23UAgHCMGQ.kFPfeSb2leWr4.CmnncIxoDraw1GL3.	other	\N	\N	\N	DMkFNZEJ41C0H7pPcVgmeMGbF52Lg5GCiwIzK1SzsxYcoHjEU4dZ53p4xDNq	2021-05-05 08:23:27	2019-11-05 08:25:39	2021-05-05 08:23:27	\N	\N	\N	3	f	viewer
44	\N	dr. Satria Pandu Persada Isma, spOT(K)	percy	percyisma@yahoo.com	\N	$2y$10$Dz/NRZJA4ukZu.DXZOrJYOshpakBq1CzcQ280zRMOmA1ciHE7srea	other	\N	\N	\N	JsVoaTYGxoVGJEz3ZVHuwSLVHgja0Hx243eh02IltI3dWQ9ndfCt1twb2PZW	2020-11-18 19:20:33	2020-05-05 06:13:06	2020-11-18 19:20:33	\N	\N	\N	3	f	viewer
45	\N	dr. Wildan Latief, SpOT(K)	latief	wildan250@gmail.com	\N	$2y$10$slCMQgOMADlp7HPQD0tpRuRw3eUH/ZfNPHm.GJyTcYL2WQ63ZtZ9e	other	\N	\N	\N	\N	\N	2020-05-05 06:16:29	2020-05-05 06:16:29	\N	\N	\N	3	f	viewer
46	\N	Dr. Ruksal Saleh, dr. Sp.OT(K)	ruksalsaleh	ruksal@gmail.com	\N	$2y$10$Ii0U0dvn0OreYGv5LelJB.TqKBomQfT/YRKprDsTX9mOKXR3g7BGO	other	\N	\N	\N	CEXSm4khptPJqWh8rLdhA4XTGVLCgJWXOkFIs254n18Xoct6r4FM5c8Kwh2g	2022-06-06 15:47:26	2020-05-05 09:14:30	2022-06-06 15:47:26	\N	\N	\N	3	f	viewer
47	\N	dr. Yudha Nathan sakti, Sp.OT(K)	yudhaUGM	yudha@gmail.com	\N	$2y$10$A4r/WigaWfUQNVjXs2IPveemug6IOiWY9WjGFlhbnMsQbmvk/vQ.G	other	\N	\N	\N	\N	\N	2020-05-05 09:28:25	2020-05-05 09:28:25	\N	\N	\N	3	f	viewer
49	\N	Dr. Ferdiandyah, dr., Sp.OT(K)	fer	ferdyortho@gmail.com	\N	$2y$10$w1EMNXsmJ31MS8txqd6NTe4DfAcNiIFp7Xzy6p7fP1CL9bARYr1Ke	other	\N	\N	\N	\N	2022-05-25 14:24:18	2020-11-17 20:41:36	2022-05-25 14:24:18	\N	\N	\N	3	f	viewer
51	\N	dr. Kukuh D Hernugrahanto, Sp.OT(K)	kukuh	kukuhdh@gmail.com	\N	$2y$10$/XvoBoxybvL.TtEBgQ9TU.v.qptiO3wcJ7bN9QM4.09tlBpExUGYO	other	\N	\N	\N	\N	2021-10-29 06:49:59	2021-05-25 21:38:43	2021-10-29 06:49:59	\N	\N	\N	3	f	viewer
52	\N	Prof. Dr. dr. Dwikora November Utomo, SpOT(K)	dwikora@ionbec.com	dwikora@ionbec.com	2025-11-04 14:19:48	$2y$12$nuiW2bF2HE/dkIm2caw9puyeeaQU129ZXpIBKjAA/jZJaL0BrndGy	other	\N	\N	\N	\N	\N	2025-11-04 14:11:23	2025-11-04 14:19:48	\N	\N	\N	3	f	viewer
53	\N	dr. Teddy Heri Wardhana, SpOT(K)	teddy@ionbec.com	teddy@ionbec.com	2025-11-04 14:19:48	$2y$12$V9y.xu1W0L9Q352KgFtEJubP2mr7J8zmJpfuodeG5WOqyQLD/eQ9W	other	\N	\N	\N	\N	\N	2025-11-04 14:11:23	2025-11-04 14:19:48	\N	\N	\N	3	f	viewer
54	\N	dr. Istan Irmansyah, SpOT(K)	istan@ionbec.com	istan@ionbec.com	2025-11-04 14:19:48	$2y$12$Jf3Lhs0jXbdaw7mmyTN4aeq9iyAQfBwUseDEXtqi0SGd9/EGOLu8W	other	\N	\N	\N	\N	\N	2025-11-04 14:11:23	2025-11-04 14:19:48	\N	\N	\N	3	f	viewer
55	\N	Dr. dr. Muhammad Sakti, SpOT(K)	muhammad@ionbec.com	muhammad@ionbec.com	2025-11-04 14:19:48	$2y$12$CnWcZXy20F1B51rv3Yg1wuR50qZI86/VnHyO6sBNaWsM7omp.Y/k2	other	\N	\N	\N	\N	\N	2025-11-04 14:11:23	2025-11-04 14:19:48	\N	\N	\N	3	f	viewer
56	\N	Dr. dr. Mouli Edward, SpOT(K)	mouli@ionbec.com	mouli@ionbec.com	2025-11-04 14:19:48	$2y$12$8REB49E0QwijFa1LvE9V.ezMrvj95ISXhFaNSS4k97fDCE0z46hEa	other	\N	\N	\N	\N	\N	2025-11-04 14:11:23	2025-11-04 14:19:48	\N	\N	\N	3	f	viewer
57	\N	dr. Syaifullah Asmiragani, SpOT(K)	syaifullah@ionbec.com	syaifullah@ionbec.com	2025-11-04 14:19:48	$2y$12$KmxhTQCBZa7rjCW9ujQqDuLeaVWk5KVNDlLmViHtuWanAqGNtasGe	other	\N	\N	\N	\N	\N	2025-11-04 14:11:23	2025-11-04 14:19:48	\N	\N	\N	3	f	viewer
58	\N	Dr. dr. Mujaddid Idulhaq, SpOT(K)	mujaddid@ionbec.com	mujaddid@ionbec.com	2025-11-04 14:19:48	$2y$12$ndTzvZa.SgqNHoAapx6VzuGBkkMGfWfdUwgZRuSwp/Gtxm2b57fE2	other	\N	\N	\N	\N	\N	2025-11-04 14:11:23	2025-11-04 14:19:48	\N	\N	\N	3	f	viewer
59	\N	Dr. dr. Ihsan Oesman, SpOT(K)	ihsan@ionbec.com	ihsan@ionbec.com	2025-11-04 14:19:48	$2y$12$zLSDg0PKL4ux9HizKsFTp.IMG.miigbS0YVDtIwPuKCqI8m0Eom/2	other	\N	\N	\N	\N	\N	2025-11-04 14:11:23	2025-11-04 14:19:48	\N	\N	\N	3	f	viewer
60	\N	Dr. dr. R. Andri Primadhi, SpOT(K)	andri@ionbec.com	andri@ionbec.com	2025-11-04 14:19:48	$2y$12$5osA/9E.OBT4gXRs9LZb9OW2tX1zm/EYc7MXoReRKMMf2kxQYbzHW	other	\N	\N	\N	\N	\N	2025-11-04 14:11:23	2025-11-04 14:19:48	\N	\N	\N	3	f	viewer
61	\N	Dr. dr. Yudha Mathan Sakti, SpOT(K)	yudha@ionbec.com	yudha@ionbec.com	2025-11-04 14:19:48	$2y$12$Ka1s16pYV.A1wMm0nHomteRZlh.l.3bT3WnIVGK1I6PdJ373hwQrC	other	\N	\N	\N	\N	\N	2025-11-04 14:11:23	2025-11-04 14:19:48	\N	\N	\N	3	f	viewer
62	\N	dr. Pranajaya Dharma Kadar, SpOT(K)	pranajaya@ionbec.com	pranajaya@ionbec.com	2025-11-04 14:19:48	$2y$12$700ea0dCIyBnGprlV4aR2ewRvcsUvwsSAwdFTduJPLmPLTMSB8AWG	other	\N	\N	\N	\N	\N	2025-11-04 14:11:23	2025-11-04 14:19:48	\N	\N	\N	3	f	viewer
63	\N	Dr. dr. Rieva Ermawan, SpOT(K)	rieva@ionbec.com	rieva@ionbec.com	2025-11-04 14:19:48	$2y$12$kI/EB78KmkH00U8auFI5CuzCn7VmmqJ7V8mfXv6BKRT2q0HXAqVwu	other	\N	\N	\N	\N	\N	2025-11-04 14:11:23	2025-11-04 14:19:48	\N	\N	\N	3	f	viewer
64	\N	Dr. dr. I Gusti Ngurah Wien Aryana, SpOT(K)	gusti@ionbec.com	gusti@ionbec.com	2025-11-04 14:19:48	$2y$12$OM8mtEEp4hKyZTsFAshn8uDFv00897wkIs2qjiuhPxMUJd4X9TZcq	other	\N	\N	\N	\N	\N	2025-11-04 14:11:23	2025-11-04 14:19:48	\N	\N	\N	3	f	viewer
65	\N	Dr. dr. Krisna Yuarno Phatama, SpOT(K)	krisna@ionbec.com	krisna@ionbec.com	2025-11-04 14:19:48	$2y$12$xoXAAFpOAwRro6VotQwJjOWfIVaVytYAG0H9A0sRgZX4NVAidkwRG	other	\N	\N	\N	\N	\N	2025-11-04 14:11:23	2025-11-04 14:19:48	\N	\N	\N	3	f	viewer
66	\N	Dr. dr. Rendra Leonas, SpOT(K)	rendra@ionbec.com	rendra@ionbec.com	2025-11-04 14:19:48	$2y$12$CSI1pSnKisBm4sUxzErTMeWQXBmEqZU1LRu2bRYCovCfPocAd3oQO	other	\N	\N	\N	\N	\N	2025-11-04 14:11:23	2025-11-04 14:19:48	\N	\N	\N	3	f	viewer
67	\N	Dr. dr. Roni Eko Sahputra, SpOT(K)	roni@ionbec.com	roni@ionbec.com	2025-11-04 14:19:48	$2y$12$WnzVi3aPTnu8QdzDv3OSOOmGX4q1.Ls9plNigsAwq2bCr9foxvTry	other	\N	\N	\N	\N	\N	2025-11-04 14:11:23	2025-11-04 14:19:48	\N	\N	\N	3	f	viewer
68	\N	Prof. Dr. dr. Azharuddin, SpOT(K)	azharuddin@ionbec.com	azharuddin@ionbec.com	2025-11-04 14:19:48	$2y$12$RCpdld5NGe2yfuXe/hAvBOl47i3n6qGiVTIhRxW38BYl3tFh0mzse	other	\N	\N	\N	\N	\N	2025-11-04 14:11:23	2025-11-04 14:19:48	\N	\N	\N	3	f	viewer
3	\N	dr. Muhammad Hardian Basuki, Sp.OT	basuki	basukimh@gmail.com	\N	$2y$10$h1ESfjigqRgkgM4LJ6KcVeg2yOJk5TAMFFIXcTUPA3e1xR.SA0xpC	other	\N	\N	\N	JuUNWPmP9NimJ8NmJSQd4O2mXwTt8pL26W4JVI82XPAZWpJNyWWZh5020LON	2022-09-13 20:48:57	2017-07-11 22:32:33	2025-11-04 15:19:37	\N	\N	\N	3	f	viewer
6	\N	Dr. dr. Hermawan Nagar Rasyid, SpOT	hermawan	hermawanphd@gmail.com	\N	$2y$10$1OOJFgeSh.5CNY0UqABpT.qJWSTHylXnfjrqntooHcdYGzCAs.OrW	other	\N	\N	\N	nOkQZCjHBBUuonfngWNhydeb6LG7qHrgMokD7ViunTtvVivZ4wUcUp1wTpS9	2018-12-19 10:45:16	2017-07-11 22:36:01	2018-12-19 10:45:16	\N	\N	\N	3	f	viewer
7	\N	Dr. dr. Mouli Edward, M.Kes, SpOT(K)	med	medortho2000@yahoo.com	\N	$2y$10$Fut5AX2z3P1xJriLn9hlFO9mJGGYZJiFHj2xZJN8LBEasq9FAckI.	other	\N	\N	\N	T1iYKuLmFc6gYiNDZFmC5J4Ktsii9IifGUz9ntpNgIukKBwuuUCAKcVcBuvR	2022-05-24 10:34:05	2017-07-11 22:45:52	2022-05-24 10:34:05	\N	\N	\N	3	f	viewer
8	\N	Dr. dr. Aryadi Kurniawan, Sp.OT	aryadi	aryadik@yahoo.com	\N	$2y$10$IW3BBdVi7xs66zfBkJmCoOJXyWwzAlvXc8yQCyYtv85Xk7FJchDEu	other	\N	\N	\N	DFxRrjQwG9MeiXbdsrUUJYNYJ4PP6nSJxyA7jxjYebJnE2u2UqmOXIQ4zOUr	2022-05-24 10:44:52	2017-07-11 22:46:49	2022-05-24 10:44:52	\N	\N	\N	3	f	viewer
9	\N	Ismail Mariyanto	ismail	ismail@gmail.com	\N	$2y$10$yaByj57xJXA3sv5xtsdabeT./zAEdNbwMM1OchqxNJMetP8HY48HC	other	\N	\N	\N	\N	2017-07-12 08:46:08	2017-07-11 22:47:23	2017-12-28 01:20:07	2017-12-28 01:20:07	\N	\N	3	f	viewer
10	\N	rahadyan magetsari	magetsari	magetsari@gmail.com	\N	$2y$10$vX2bLy6m9zl5e6U2AMT5JOtGM.sCqBJKBo9OWs7FlRUbqlyUiryia	other	\N	\N	\N	\N	2017-07-12 12:04:53	2017-07-11 22:47:56	2017-07-23 03:13:01	2017-07-23 03:13:01	\N	\N	3	f	viewer
11	\N	Prof.Dr.dr. I Ketut Siki Kawiyana, SpB, SPOT	siki	siki_kawiyana@hotmail.com	\N	$2y$10$htC0QUgyaKQCLJkMf8wgy.NlGGVQ9xwjxyLnAxILr.sLvBWP5nZye	other	\N	\N	\N	YwV38TStpV2PUviIczLKGCZOtnQI4xtW65nOg9COuoRLZbopIzJFjMiiTYKx	2018-01-04 10:20:25	2017-07-11 22:48:47	2018-07-10 07:58:26	2018-07-10 07:58:26	\N	\N	3	f	viewer
13	\N	Yoppi	yoppi	yoppi.ari@gmail.com	\N	$2y$10$Zj4qiyz2umWsHCeX07ykV.HmOjPNxmVau4nT0WG1fpyNmZVX5zLP6	other	\N	\N	\N	fkftovfwbIhjfmar6Re6VyAX3QWazw3a27GXDtT6T6DEqXpBVL8hyoj6U6cp	2019-10-01 18:22:04	2017-07-12 02:29:37	2019-10-01 18:22:04	\N	\N	\N	3	f	viewer
14	\N	Dr. Dwikora Novembri Utomo SpOT(K)	dwikora	dwikora@gmail.com	\N	$2y$10$gQW//Npn9TsvqPVKMbpVGelGqUW9EFkNA5fk/YzXz.spzor7iaE46	other	\N	\N	\N	ITaYCePrvj5sAG0XvtGwrT9sshaVigBKKepnEbHSXUX2gRuiv8iRlUYh7ZVA	2020-05-08 08:36:58	2017-07-12 08:48:23	2020-11-17 20:48:32	\N	\N	\N	3	f	viewer
15	\N	Dr. dr. Istan Irmansyah SpOT	istan	istan_irmansyah@yahoo.com	\N	$2y$10$e9u4JIk/q6oeC0ZhKNKGweZzh6Wy2gAldYs4do5AvMkh3Xnw6NQRa	other	\N	\N	\N	\N	2017-07-12 08:49:13	2017-07-12 08:48:59	2018-07-10 07:58:10	2018-07-10 07:58:10	\N	\N	3	f	viewer
16	\N	Dr. dr. Ferdiansyah, Sp.OT	ferdiansyah	ferdyortho@yahoo.com	\N	$2y$10$pgx.HKnicL6pWMo1QU9ljO/p4lP3KbJoqzFxarpHP9a36b8J7klaO	other	\N	\N	\N	kZ7eUo0k9xQCkhjPVSl7xbtmmZCvhQ4lDq3hV5DB4jyiJhcegc74GwClWiYR	2020-10-16 15:00:24	2017-07-12 12:30:25	2020-11-17 20:39:57	\N	\N	\N	3	f	viewer
18	\N	Dr. dr. Muhammad Sakti,SpOT(K)	sakti	saktiortho96@yahoo.co.id	\N	$2y$10$/NLfmNKHwgIog/JZadA9j.pNEhEOdKwXaOhBzxsTDVA7obik0d/.m	other	\N	\N	\N	KI7Gi0t9AztV8iFoqurnkdaY5Ofj00uvTaooLTKjLi9S71Jm8GsOBhB15wEN	2022-09-12 11:09:53	2017-12-28 01:06:09	2022-09-12 11:09:53	\N	\N	\N	3	f	viewer
21	\N	Dr. dr. Pamudji Utomo, SpOT(K)	pamudji	utomodr@yahoo.com	\N	$2y$10$/z.Eg2mASRCSrFUeOWlI1Oi7ASEHajXb.5xfhv9C2nxPWKLRatrci	other	\N	\N	\N	URZfVfCzVSc6aB2bDhddTRzgr3acRQslveHgHTNFf8ckgmxmEp1fZZeKkALz	2022-05-18 08:04:35	2017-12-28 01:10:05	2022-05-18 08:04:35	\N	\N	\N	3	f	viewer
24	\N	Dr. dr. Gede Eka Wiratnaya, SpOT(K)	eka	ekawiratnaya@gmail.com	\N	$2y$10$j0g7moqqsgi53ifDyRfpfeAzC4cGznXVWVD/73Hf76T1CJdcFXoEW	other	\N	\N	\N	6kKPCxCELEPBsfFPO1lD3Kw18f3rgrL4YbhIF8JdCXxvGBWVWpgJ6Zw5Bbnp	2020-05-05 10:19:52	2017-12-28 01:14:54	2020-05-05 10:19:52	\N	\N	\N	3	f	viewer
25	\N	dr. Krisna Yuarno Phatama, SpOT	krisna	krisnayuarno@gmail.com	\N	$2y$10$XJCJvbc9DwpjNN6cAQmwy.Pi7PCRjloponaoyqWFtIBph3xNszGB6	other	\N	\N	\N	\N	2017-12-28 08:15:39	2017-12-28 01:16:49	2018-01-04 07:58:03	2018-01-04 07:58:03	\N	\N	3	f	viewer
26	\N	dr. Wildan Latief, SpOT	wildan	wildan2510@gmail.com	\N	$2y$10$0nsJoY058NHax8N7F8HQauWZ3MeUU.k2eZk62ZU/6cPa9WUkWJV46	other	\N	\N	\N	LZizu0aGBfAlie5VObQrqxVkSXDNpIR4lA3m1pXcSDvRdv1uMnBcZND1EBuh	2017-12-28 10:55:35	2017-12-28 01:21:50	2018-01-04 07:57:49	2018-01-04 07:57:49	\N	\N	3	f	viewer
27	\N	dr. Nadia Nastassia PPSI, SpOT	nadia	nadia.nastassia@gmail.com	\N	$2y$10$Gfdz8Smpru0212LuMsbw9e.JoIXfWOQ7FSR4A09QRvGmCwurirHKG	other	\N	\N	\N	1cCw5woBAgGJhuGaLkT1ILS7esHAZXURUqiaLUCFlCHSnJs4s34xtvk0SMcA	2017-12-28 09:27:21	2017-12-28 01:22:28	2018-01-04 07:57:36	2018-01-04 07:57:36	\N	\N	3	f	viewer
28	\N	Dr. dr. Ruksal Saleh, Sp.OT	ruksal	ruksal_saleh@yahoo.com	\N	$2y$10$tCujNBsO.GwaUdOWq2LcOOs/KCCZ2fi.df8rNQBg7t0/3qfzzUSdi	other	\N	\N	\N	9uqblKj7rFyksbQz6TpnD2g0ujmHRD63xsPp5DW3LPqIIIOHizmywCEXCb7R	2017-12-28 10:05:41	2017-12-28 01:28:45	2018-07-10 07:58:00	2018-07-10 07:58:00	\N	\N	3	f	viewer
31	\N	Rifki W Alhuraibi	veelasky	veelasky@gmail.com	\N	$2y$10$8ZrvpD9ZqLfxRclgRHDuZ.W8k/Vh9paTP/wwHQTEr1tMyquSK1wny	other	\N	\N	\N	o27N0Mo47IhGEKBd4sIABOwYTxygpdWDGWqOpFIEm8Qyp782pRjTqJeCpfy8	2018-01-03 23:52:41	2018-01-03 23:41:01	2018-07-10 07:57:52	2018-07-10 07:57:52	\N	\N	3	f	viewer
32	\N	Dr. dr. Rahadyan Magetsari, SpOT(K)	rahadyan	rahardian@gmail.com	\N	$2y$10$LnLfQCA1rAYgNMdIEPjDmedYhL44n4taZa9cmopt/dwW7EOWhmtaa	other	\N	\N	\N	6sjWgwk5LKIgYinMwStHUBs8wKlZZf5Hkl7lBS6cX1ZdagxeB3IEEhknVxPr	2021-05-27 09:42:14	2018-01-04 07:22:55	2021-05-27 09:42:14	\N	\N	\N	3	f	viewer
33	\N	dr. Renaldi Prasetia HNR, M.Kes(AIFO), SpOT	renaldi	renaldi.prasetia@gmail.com	\N	$2y$10$4Rf6kS206fB6a3ghiJaf1.yKBtKEe5luChdIi1l9O899DPmTHtlMG	other	\N	\N	\N	Us9XiMjIllDkeRGAuhee3kOwqiTbYxCIkN7beMSWF6SgCCyGvyusb53BgSMi	2020-05-05 09:09:47	2018-07-10 07:44:40	2020-05-05 09:09:47	\N	\N	\N	3	f	viewer
35	\N	dr. wildan latief	wildan.l	wildan@gmail.com	\N	$2y$10$Wb5uMb4U6MZ/IqlHsPcUYeXlawCuJaU0e4g/D2UNTgKuo8sZfxN.q	other	\N	\N	\N	\N	\N	2018-07-10 10:55:59	2018-07-17 19:51:38	2018-07-17 19:51:38	\N	\N	3	f	viewer
30	\N	dr. Syaifullah  Asmiragani, Sp.OT(K)	syaiful	syaifullahag@yahoo.com	\N	$2y$10$BEgzi3c04bZdArYpaBW3ruMG1efBF.G82JL1HKvYmqEIL/ADBka2G	other	\N	\N	\N	mOaq1GHWLmczrc0tqhxMEGlJqTC4IEoW9jefFSragzSg0xFbSJvFAoU6L9KE	2022-05-24 10:45:41	2017-12-28 01:30:20	2025-11-04 13:43:59	\N	\N	\N	3	f	viewer
23	\N	dr. Luthfi Hidayat, SpOT	luthfi	hidayat.luthfi@gmail.com	\N	$2y$10$mRT/ksCQyDnDCr0JT43tv.3Akj6FIbFHWRpEQz6sMQo9DI1ViXbIW	other	\N	\N	\N	tWWMYexBCld3NFDkNfTOasvgSHR7UXPUid72o8JazeqCw4OYUpnQKpCkGBfY	2017-12-28 08:47:10	2017-12-28 01:12:34	2025-11-04 13:44:02	2018-01-04 07:57:27	\N	\N	3	f	viewer
17	\N	Ionbec	ionbec	ionbec@ionbec.com	\N	$2y$10$5SXQxcGdVkgkrQxc/546me3uBvHAP68U2mZ6t3AHLDc3OH3aHr5R.	other	\N	\N	\N	f1dd74aSSCerNMxMrjMQHjlBn4VG13jnY6haCnNR4N77qGlPi4LCewDOrlF3	2018-01-04 10:50:42	2017-12-27 20:30:01	2025-11-04 13:44:00	\N	\N	\N	3	f	viewer
19	\N	Dr. dr. Yoyos Dias Ismiarto, SpOT(K), M.Kes, CCD	yoyos	yosismiarto@hotmail.com	\N	$2y$10$jaaKYULKSP1ME1h4nQu0sOuAjo7dy0p34a4.ZgeMZOQrpXCZYLGwq	other	\N	\N	\N	C15ActyjSBVOlLBPF4mW68sOzA8DXSbLQRDzpW69eZQOptbRV72uQ3UfcYx0	2022-05-24 10:14:14	2017-12-28 01:08:07	2025-11-04 13:44:01	\N	\N	\N	3	f	viewer
29	\N	dr. Teddy H. Wardhana, Sp.OT	teddy	thwardhana@hotmail.com	\N	$2y$10$PZMKsBN4VdXIzLckA82HmOndZvSPsbgllww6U9M6o1HY.THhanL4u	other	\N	\N	\N	UlN2OZT5xvOIofImVKYjYEJPu7jWzBjdKBSqMaRRVNiVgtkZaNHzBgLFRoct	2022-09-12 10:53:34	2017-12-28 01:29:30	2025-11-04 13:43:58	\N	\N	\N	3	f	viewer
34	\N	dr. Yudha Mathan Sakti, SpOT(K)	yudha	mathan_sakti@yahoo.com	\N	$2y$10$p2dUtH5hT5.ViPTmldwMPewOrdsI.eEDNEmqYCNhHWy1kx7S05ib2	other	\N	\N	\N	GRa5agPkD0VbiLRS6QnqdMkwSx2ArXjjN7rTAo0p1eqOHhnveaNDGCNih7jw	2021-05-27 15:00:36	2018-07-10 07:53:40	2025-11-04 13:44:00	\N	\N	\N	3	f	viewer
20	\N	dr Jainal Arifin, SpOT(K)	jainal	ia.jenal@yahoo.co.id	\N	$2y$10$g4fIN0ATdVDXCZ78cUrZiuAiQDCHvkVhAjJ.2kFizv3fjnVEFqAsq	other	\N	\N	\N	2DHJFJFa3oBVjFxy41wM8uHMfmMiOwXQzR7fmCzQw1IghqQScGIXnCzZQv7T	2020-05-05 09:34:44	2017-12-28 01:09:15	2025-11-04 13:44:01	\N	\N	\N	3	f	viewer
22	\N	dr. Mujaddid Idulhaq, SpOT(K)	mujaddid	idulhaq@yahoo.com	\N	$2y$10$VHg5u4Mzu/kEX7aWNqg8GupuSOYQQ.idpkZStq/H5pie2WmGj2oBK	other	\N	\N	\N	ZoJgqn0AX4sDTfO1zhoPltVZOr4ZFJ7G7crPT58apzgnIcxVguxmAt7jWUkG	2022-09-12 10:37:10	2017-12-28 01:11:30	2025-11-04 13:44:02	\N	\N	\N	3	f	viewer
12	\N	Dr. dr. Edi Mustamsir, SpOT(K)	edi	edimustamsir@yahoo.co.id	\N	$2y$10$j430r8nV/em.jPVbfHb09.63MmEu2sxq27BV0P6FWHRd2rauSCQXW	other	\N	\N	\N	Vj8hMJRIEFKscinL9mHHtvZ3PhR8f2RjCL4sSjtSc9Lz9PLByq5HRTp3tURZ	2021-05-05 08:02:02	2017-07-11 22:49:34	2021-05-05 08:02:02	\N	\N	\N	3	f	viewer
\.


--
-- Name: activity_log_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.activity_log_id_seq', 4, true);


--
-- Name: answers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.answers_id_seq', 570, true);


--
-- Name: attempt_question_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.attempt_question_id_seq', 4, true);


--
-- Name: attempts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.attempts_id_seq', 2, true);


--
-- Name: categories_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.categories_id_seq', 42, false);


--
-- Name: clients_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.clients_id_seq', 4, false);


--
-- Name: deliveries_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.deliveries_id_seq', 154, true);


--
-- Name: delivery_snapshots_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.delivery_snapshots_id_seq', 4, true);


--
-- Name: exam_session_logs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.exam_session_logs_id_seq', 1, false);


--
-- Name: exams_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.exams_id_seq', 44, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: groups_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.groups_id_seq', 2, true);


--
-- Name: items_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.items_id_seq', 826, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 53, true);


--
-- Name: permission_role_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.permission_role_id_seq', 1, false);


--
-- Name: permission_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.permission_user_id_seq', 1, false);


--
-- Name: permissions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.permissions_id_seq', 1, false);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 1, false);


--
-- Name: questions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.questions_id_seq', 1170, true);


--
-- Name: register_data_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.register_data_id_seq', 1, false);


--
-- Name: role_user_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.role_user_id_seq', 28, true);


--
-- Name: roles_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.roles_id_seq', 1, false);


--
-- Name: takers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.takers_id_seq', 40, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 68, true);


--
-- Name: activity_log activity_log_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.activity_log
    ADD CONSTRAINT activity_log_pkey PRIMARY KEY (id);


--
-- Name: answers answers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.answers
    ADD CONSTRAINT answers_pkey PRIMARY KEY (id);


--
-- Name: attachments attachments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attachments
    ADD CONSTRAINT attachments_pkey PRIMARY KEY (id);


--
-- Name: attempt_question attempt_question_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attempt_question
    ADD CONSTRAINT attempt_question_pkey PRIMARY KEY (id);


--
-- Name: attempts attempts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attempts
    ADD CONSTRAINT attempts_pkey PRIMARY KEY (id);


--
-- Name: categories categories_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_pkey PRIMARY KEY (id);


--
-- Name: clients clients_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clients
    ADD CONSTRAINT clients_pkey PRIMARY KEY (id);


--
-- Name: clients clients_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.clients
    ADD CONSTRAINT clients_slug_unique UNIQUE (slug);


--
-- Name: deliveries deliveries_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.deliveries
    ADD CONSTRAINT deliveries_pkey PRIMARY KEY (id);


--
-- Name: delivery_snapshots delivery_snapshots_delivery_id_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.delivery_snapshots
    ADD CONSTRAINT delivery_snapshots_delivery_id_unique UNIQUE (delivery_id);


--
-- Name: delivery_snapshots delivery_snapshots_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.delivery_snapshots
    ADD CONSTRAINT delivery_snapshots_pkey PRIMARY KEY (id);


--
-- Name: exam_session_logs exam_session_logs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.exam_session_logs
    ADD CONSTRAINT exam_session_logs_pkey PRIMARY KEY (id);


--
-- Name: exams exams_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.exams
    ADD CONSTRAINT exams_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: groups groups_code_client_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groups
    ADD CONSTRAINT groups_code_client_unique UNIQUE (code, client_id);


--
-- Name: groups groups_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groups
    ADD CONSTRAINT groups_pkey PRIMARY KEY (id);


--
-- Name: items items_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.items
    ADD CONSTRAINT items_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: permission_role permission_role_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permission_role
    ADD CONSTRAINT permission_role_pkey PRIMARY KEY (id);


--
-- Name: permission_user permission_user_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permission_user
    ADD CONSTRAINT permission_user_pkey PRIMARY KEY (id);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: questions questions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT questions_pkey PRIMARY KEY (id);


--
-- Name: register_data register_data_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.register_data
    ADD CONSTRAINT register_data_pkey PRIMARY KEY (id);


--
-- Name: role_user role_user_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_user
    ADD CONSTRAINT role_user_pkey PRIMARY KEY (id);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: roles roles_slug_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_slug_unique UNIQUE (slug);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: takers takers_email_client_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.takers
    ADD CONSTRAINT takers_email_client_unique UNIQUE (email, client_id);


--
-- Name: takers takers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.takers
    ADD CONSTRAINT takers_pkey PRIMARY KEY (id);


--
-- Name: users users_email_client_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_client_unique UNIQUE (email, client_id);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: users users_username_client_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_username_client_unique UNIQUE (username, client_id);


--
-- Name: activity_log_log_name_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX activity_log_log_name_index ON public.activity_log USING btree (log_name);


--
-- Name: answers_hash_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX answers_hash_index ON public.answers USING btree (hash);


--
-- Name: attachables_attachment_id_attachable_id_attachable_type_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX attachables_attachment_id_attachable_id_attachable_type_index ON public.attachables USING btree (attachment_id, attachable_id, attachable_type);


--
-- Name: attachments_client_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX attachments_client_id_index ON public.attachments USING btree (client_id);


--
-- Name: attempts_client_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX attempts_client_id_index ON public.attempts USING btree (client_id);


--
-- Name: attempts_hash_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX attempts_hash_index ON public.attempts USING btree (hash);


--
-- Name: categories_client_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX categories_client_id_index ON public.categories USING btree (client_id);


--
-- Name: categories_hash_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX categories_hash_index ON public.categories USING btree (hash);


--
-- Name: deliveries_client_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX deliveries_client_id_index ON public.deliveries USING btree (client_id);


--
-- Name: deliveries_hash_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX deliveries_hash_index ON public.deliveries USING btree (hash);


--
-- Name: delivery_snapshots_delivery_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX delivery_snapshots_delivery_id_index ON public.delivery_snapshots USING btree (delivery_id);


--
-- Name: delivery_snapshots_exam_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX delivery_snapshots_exam_id_index ON public.delivery_snapshots USING btree (exam_id);


--
-- Name: exam_session_logs_attempt_id_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX exam_session_logs_attempt_id_created_at_index ON public.exam_session_logs USING btree (attempt_id, created_at);


--
-- Name: exam_session_logs_ip_address_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX exam_session_logs_ip_address_created_at_index ON public.exam_session_logs USING btree (ip_address, created_at);


--
-- Name: exam_session_logs_session_key_created_at_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX exam_session_logs_session_key_created_at_index ON public.exam_session_logs USING btree (session_key, created_at);


--
-- Name: exams_client_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX exams_client_id_index ON public.exams USING btree (client_id);


--
-- Name: exams_hash_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX exams_hash_index ON public.exams USING btree (hash);


--
-- Name: groups_client_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX groups_client_id_index ON public.groups USING btree (client_id);


--
-- Name: groups_hash_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX groups_hash_index ON public.groups USING btree (hash);


--
-- Name: items_client_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX items_client_id_index ON public.items USING btree (client_id);


--
-- Name: items_hash_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX items_hash_index ON public.items USING btree (hash);


--
-- Name: password_resets_email_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX password_resets_email_index ON public.password_resets USING btree (email);


--
-- Name: permission_role_permission_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX permission_role_permission_id_index ON public.permission_role USING btree (permission_id);


--
-- Name: permission_role_role_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX permission_role_role_id_index ON public.permission_role USING btree (role_id);


--
-- Name: permission_user_permission_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX permission_user_permission_id_index ON public.permission_user USING btree (permission_id);


--
-- Name: permission_user_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX permission_user_user_id_index ON public.permission_user USING btree (user_id);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: questions_client_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX questions_client_id_index ON public.questions USING btree (client_id);


--
-- Name: questions_hash_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX questions_hash_index ON public.questions USING btree (hash);


--
-- Name: role_user_role_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX role_user_role_id_index ON public.role_user USING btree (role_id);


--
-- Name: role_user_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX role_user_user_id_index ON public.role_user USING btree (user_id);


--
-- Name: roles_client_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX roles_client_id_index ON public.roles USING btree (client_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: takers_client_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX takers_client_id_index ON public.takers USING btree (client_id);


--
-- Name: users_client_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_client_id_index ON public.users USING btree (client_id);


--
-- Name: users_is_admin_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX users_is_admin_index ON public.users USING btree (is_admin);


--
-- Name: answers answers_question_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.answers
    ADD CONSTRAINT answers_question_id_foreign FOREIGN KEY (question_id) REFERENCES public.questions(id) ON DELETE CASCADE;


--
-- Name: attachables attachables_attachment_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attachables
    ADD CONSTRAINT attachables_attachment_id_foreign FOREIGN KEY (attachment_id) REFERENCES public.attachments(id) ON DELETE CASCADE;


--
-- Name: attachments attachments_client_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attachments
    ADD CONSTRAINT attachments_client_id_foreign FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- Name: attachments attachments_uploaded_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attachments
    ADD CONSTRAINT attachments_uploaded_by_foreign FOREIGN KEY (uploaded_by) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: attempt_question attempt_question_attempt_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attempt_question
    ADD CONSTRAINT attempt_question_attempt_id_foreign FOREIGN KEY (attempt_id) REFERENCES public.attempts(id) ON DELETE CASCADE;


--
-- Name: attempt_question attempt_question_question_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attempt_question
    ADD CONSTRAINT attempt_question_question_id_foreign FOREIGN KEY (question_id) REFERENCES public.questions(id) ON DELETE CASCADE;


--
-- Name: attempts attempts_attempted_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attempts
    ADD CONSTRAINT attempts_attempted_by_foreign FOREIGN KEY (attempted_by) REFERENCES public.takers(id) ON DELETE CASCADE;


--
-- Name: attempts attempts_client_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attempts
    ADD CONSTRAINT attempts_client_id_foreign FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- Name: attempts attempts_delivery_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attempts
    ADD CONSTRAINT attempts_delivery_id_foreign FOREIGN KEY (delivery_id) REFERENCES public.deliveries(id) ON DELETE CASCADE;


--
-- Name: attempts attempts_exam_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.attempts
    ADD CONSTRAINT attempts_exam_id_foreign FOREIGN KEY (exam_id) REFERENCES public.exams(id) ON DELETE CASCADE;


--
-- Name: categories categories_client_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.categories
    ADD CONSTRAINT categories_client_id_foreign FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- Name: category_item category_item_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.category_item
    ADD CONSTRAINT category_item_category_id_foreign FOREIGN KEY (category_id) REFERENCES public.categories(id) ON DELETE CASCADE;


--
-- Name: category_item category_item_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.category_item
    ADD CONSTRAINT category_item_item_id_foreign FOREIGN KEY (item_id) REFERENCES public.items(id) ON DELETE CASCADE;


--
-- Name: category_question category_question_category_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.category_question
    ADD CONSTRAINT category_question_category_id_foreign FOREIGN KEY (category_id) REFERENCES public.categories(id) ON DELETE CASCADE;


--
-- Name: category_question category_question_question_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.category_question
    ADD CONSTRAINT category_question_question_id_foreign FOREIGN KEY (question_id) REFERENCES public.questions(id) ON DELETE CASCADE;


--
-- Name: deliveries deliveries_client_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.deliveries
    ADD CONSTRAINT deliveries_client_id_foreign FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- Name: deliveries deliveries_exam_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.deliveries
    ADD CONSTRAINT deliveries_exam_id_foreign FOREIGN KEY (exam_id) REFERENCES public.exams(id) ON DELETE CASCADE;


--
-- Name: deliveries deliveries_group_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.deliveries
    ADD CONSTRAINT deliveries_group_id_foreign FOREIGN KEY (group_id) REFERENCES public.groups(id) ON DELETE CASCADE;


--
-- Name: delivery_snapshots delivery_snapshots_delivery_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.delivery_snapshots
    ADD CONSTRAINT delivery_snapshots_delivery_id_foreign FOREIGN KEY (delivery_id) REFERENCES public.deliveries(id) ON DELETE CASCADE;


--
-- Name: delivery_snapshots delivery_snapshots_exam_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.delivery_snapshots
    ADD CONSTRAINT delivery_snapshots_exam_id_foreign FOREIGN KEY (exam_id) REFERENCES public.exams(id) ON DELETE CASCADE;


--
-- Name: delivery_taker delivery_taker_delivery_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.delivery_taker
    ADD CONSTRAINT delivery_taker_delivery_id_foreign FOREIGN KEY (delivery_id) REFERENCES public.deliveries(id) ON DELETE CASCADE;


--
-- Name: delivery_taker delivery_taker_taker_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.delivery_taker
    ADD CONSTRAINT delivery_taker_taker_id_foreign FOREIGN KEY (taker_id) REFERENCES public.takers(id) ON DELETE CASCADE;


--
-- Name: exam_item exam_item_exam_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.exam_item
    ADD CONSTRAINT exam_item_exam_id_foreign FOREIGN KEY (exam_id) REFERENCES public.exams(id) ON DELETE CASCADE;


--
-- Name: exam_item exam_item_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.exam_item
    ADD CONSTRAINT exam_item_item_id_foreign FOREIGN KEY (item_id) REFERENCES public.items(id) ON DELETE CASCADE;


--
-- Name: exam_session_logs exam_session_logs_attempt_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.exam_session_logs
    ADD CONSTRAINT exam_session_logs_attempt_id_foreign FOREIGN KEY (attempt_id) REFERENCES public.attempts(id) ON DELETE CASCADE;


--
-- Name: exams exams_client_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.exams
    ADD CONSTRAINT exams_client_id_foreign FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- Name: group_taker group_taker_group_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.group_taker
    ADD CONSTRAINT group_taker_group_id_foreign FOREIGN KEY (group_id) REFERENCES public.groups(id) ON DELETE CASCADE;


--
-- Name: group_taker group_taker_taker_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.group_taker
    ADD CONSTRAINT group_taker_taker_id_foreign FOREIGN KEY (taker_id) REFERENCES public.takers(id) ON DELETE CASCADE;


--
-- Name: groups groups_client_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.groups
    ADD CONSTRAINT groups_client_id_foreign FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- Name: items items_client_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.items
    ADD CONSTRAINT items_client_id_foreign FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- Name: permission_role permission_role_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permission_role
    ADD CONSTRAINT permission_role_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: permission_role permission_role_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permission_role
    ADD CONSTRAINT permission_role_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: permission_user permission_user_permission_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permission_user
    ADD CONSTRAINT permission_user_permission_id_foreign FOREIGN KEY (permission_id) REFERENCES public.permissions(id) ON DELETE CASCADE;


--
-- Name: permission_user permission_user_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.permission_user
    ADD CONSTRAINT permission_user_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: questions questions_client_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT questions_client_id_foreign FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- Name: questions questions_item_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.questions
    ADD CONSTRAINT questions_item_id_foreign FOREIGN KEY (item_id) REFERENCES public.items(id) ON DELETE CASCADE;


--
-- Name: role_user role_user_role_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_user
    ADD CONSTRAINT role_user_role_id_foreign FOREIGN KEY (role_id) REFERENCES public.roles(id) ON DELETE CASCADE;


--
-- Name: role_user role_user_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.role_user
    ADD CONSTRAINT role_user_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: roles roles_client_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_client_id_foreign FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- Name: roles roles_parent_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_parent_id_foreign FOREIGN KEY (parent_id) REFERENCES public.roles(id);


--
-- Name: takers takers_client_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.takers
    ADD CONSTRAINT takers_client_id_foreign FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- Name: users users_client_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_client_id_foreign FOREIGN KEY (client_id) REFERENCES public.clients(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict wWmvEhqT2YAbTxBJr66BtlZKV1nx2XoG3ZReBqUy4AEi4IAb35CScik869vVJgn

