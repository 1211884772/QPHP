/*
 Navicat Premium Data Transfer

 Source Server         : ubuntu-oracle-qphp
 Source Server Type    : Oracle
 Source Server Version : 110200
 Source Host           : 192.168.123.101:1521
 Source Schema         : QPHP

 Target Server Type    : Oracle
 Target Server Version : 110200
 File Encoding         : 65001

 Date: 08/06/2022 15:16:49
*/


-- ----------------------------
-- Table structure for mm_address
-- ----------------------------
DROP TABLE "QPHP"."mm_address";
CREATE TABLE "QPHP"."mm_address" (
  "id" NUMBER(20,0) NOT NULL,
  "user_id" NUMBER(20,0) NOT NULL,
  "name" NVARCHAR2(255) NOT NULL,
  "address_info" NVARCHAR2(255) NOT NULL,
  "is_default" NUMBER(4,0) NOT NULL
)
LOGGING
NOCOMPRESS
PCTFREE 10
INITRANS 1
STORAGE (
  INITIAL 65536 
  NEXT 1048576 
  MINEXTENTS 1
  MAXEXTENTS 2147483645
  BUFFER_POOL DEFAULT
)
PARALLEL 1
NOCACHE
DISABLE ROW MOVEMENT
;

-- ----------------------------
-- Records of mm_address
-- ----------------------------
INSERT INTO "QPHP"."mm_address" VALUES ('1', '1', 'qmumu', '中国陕西西安莲湖区', '1');

-- ----------------------------
-- Table structure for mm_user
-- ----------------------------
DROP TABLE "QPHP"."mm_user";
CREATE TABLE "QPHP"."mm_user" (
  "id" NUMBER(20,0) NOT NULL,
  "username" NVARCHAR2(255),
  "age" NUMBER(4,0),
  "address" NVARCHAR2(255),
  "pwd" NVARCHAR2(255)
)
LOGGING
NOCOMPRESS
PCTFREE 10
INITRANS 1
STORAGE (
  INITIAL 65536 
  NEXT 1048576 
  MINEXTENTS 1
  MAXEXTENTS 2147483645
  BUFFER_POOL DEFAULT
)
PARALLEL 1
NOCACHE
DISABLE ROW MOVEMENT
;

-- ----------------------------
-- Records of mm_user
-- ----------------------------
INSERT INTO "QPHP"."mm_user" VALUES ('1', 'QPHP', '1', '西安1', '123123');
INSERT INTO "QPHP"."mm_user" VALUES ('2', 'mumu', '2', '上海', '12312312');
INSERT INTO "QPHP"."mm_user" VALUES ('3', 'uer', '1', '北京', '12312');
INSERT INTO "QPHP"."mm_user" VALUES ('4', 'vrvr', '34', '电商', '12312312');
INSERT INTO "QPHP"."mm_user" VALUES ('8', 'mumu', '0', NULL, '123456');

-- ----------------------------
-- Table structure for mm_user_info
-- ----------------------------
DROP TABLE "QPHP"."mm_user_info";
CREATE TABLE "QPHP"."mm_user_info" (
  "id" NUMBER(20,0) NOT NULL,
  "user_id" NUMBER(20,0) NOT NULL,
  "birthday" NUMBER(11,0) NOT NULL,
  "name" NVARCHAR2(255) NOT NULL,
  "info" NVARCHAR2(255) NOT NULL
)
LOGGING
NOCOMPRESS
PCTFREE 10
INITRANS 1
STORAGE (
  INITIAL 65536 
  NEXT 1048576 
  MINEXTENTS 1
  MAXEXTENTS 2147483645
  BUFFER_POOL DEFAULT
)
PARALLEL 1
NOCACHE
DISABLE ROW MOVEMENT
;
COMMENT ON COLUMN "QPHP"."mm_user_info"."info" IS '简介';

-- ----------------------------
-- Records of mm_user_info
-- ----------------------------
INSERT INTO "QPHP"."mm_user_info" VALUES ('1', '1', '599068800', '委屈委屈', '手动阀手动阀手动阀手动阀撒旦');

-- ----------------------------
-- Primary Key structure for table mm_address
-- ----------------------------
ALTER TABLE "QPHP"."mm_address" ADD CONSTRAINT "SYS_C0010831" PRIMARY KEY ("id");

-- ----------------------------
-- Checks structure for table mm_address
-- ----------------------------
ALTER TABLE "QPHP"."mm_address" ADD CONSTRAINT "SYS_C0010819" CHECK ("id" IS NOT NULL) NOT DEFERRABLE INITIALLY IMMEDIATE NORELY VALIDATE;
ALTER TABLE "QPHP"."mm_address" ADD CONSTRAINT "SYS_C0010820" CHECK ("user_id" IS NOT NULL) NOT DEFERRABLE INITIALLY IMMEDIATE NORELY VALIDATE;
ALTER TABLE "QPHP"."mm_address" ADD CONSTRAINT "SYS_C0010823" CHECK ("name" IS NOT NULL) NOT DEFERRABLE INITIALLY IMMEDIATE NORELY VALIDATE;
ALTER TABLE "QPHP"."mm_address" ADD CONSTRAINT "SYS_C0010825" CHECK ("address_info" IS NOT NULL) NOT DEFERRABLE INITIALLY IMMEDIATE NORELY VALIDATE;
ALTER TABLE "QPHP"."mm_address" ADD CONSTRAINT "SYS_C0010827" CHECK ("is_default" IS NOT NULL) NOT DEFERRABLE INITIALLY IMMEDIATE NORELY VALIDATE;

-- ----------------------------
-- Primary Key structure for table mm_user
-- ----------------------------
ALTER TABLE "QPHP"."mm_user" ADD CONSTRAINT "SYS_C0010829" PRIMARY KEY ("id");

-- ----------------------------
-- Checks structure for table mm_user
-- ----------------------------
ALTER TABLE "QPHP"."mm_user" ADD CONSTRAINT "SYS_C0010818" CHECK ("id" IS NOT NULL) NOT DEFERRABLE INITIALLY IMMEDIATE NORELY VALIDATE;

-- ----------------------------
-- Primary Key structure for table mm_user_info
-- ----------------------------
ALTER TABLE "QPHP"."mm_user_info" ADD CONSTRAINT "SYS_C0010830" PRIMARY KEY ("id");

-- ----------------------------
-- Checks structure for table mm_user_info
-- ----------------------------
ALTER TABLE "QPHP"."mm_user_info" ADD CONSTRAINT "SYS_C0010821" CHECK ("id" IS NOT NULL) NOT DEFERRABLE INITIALLY IMMEDIATE NORELY VALIDATE;
ALTER TABLE "QPHP"."mm_user_info" ADD CONSTRAINT "SYS_C0010822" CHECK ("user_id" IS NOT NULL) NOT DEFERRABLE INITIALLY IMMEDIATE NORELY VALIDATE;
ALTER TABLE "QPHP"."mm_user_info" ADD CONSTRAINT "SYS_C0010824" CHECK ("birthday" IS NOT NULL) NOT DEFERRABLE INITIALLY IMMEDIATE NORELY VALIDATE;
ALTER TABLE "QPHP"."mm_user_info" ADD CONSTRAINT "SYS_C0010826" CHECK ("name" IS NOT NULL) NOT DEFERRABLE INITIALLY IMMEDIATE NORELY VALIDATE;
ALTER TABLE "QPHP"."mm_user_info" ADD CONSTRAINT "SYS_C0010828" CHECK ("info" IS NOT NULL) NOT DEFERRABLE INITIALLY IMMEDIATE NORELY VALIDATE;