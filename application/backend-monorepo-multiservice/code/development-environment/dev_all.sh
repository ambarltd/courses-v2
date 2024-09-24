#!/bin/bash
set -e

./dev_a_start.sh
./dev_depen_check.sh
./dev_linter_check.sh
./dev_test.sh
./dev_type_check.sh
./dev_z_shutdown.sh