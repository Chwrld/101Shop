-- Migration: make `dop` column nullable in service_reports
-- This migration sets `dop` field to default NULL

ALTER TABLE `service_reports`
  MODIFY `dop` date DEFAULT NULL,
  MODIFY `date_pulled_out` date DEFAULT NULL;