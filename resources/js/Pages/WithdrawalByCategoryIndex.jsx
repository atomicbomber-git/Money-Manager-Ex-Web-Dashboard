import React, {Component} from 'react';
import moment from "moment";
import _ from "lodash"
import {Button, Card, Input, Modal, Select, Space, Table} from "antd";
import Decimal from "decimal.js";
import TransactionIndex from "./TransactionIndex.jsx";
import {formatCurrency} from "../utilities.js";
import AppLayout from "./AppLayout.jsx";
import {ReloadOutlined} from "@ant-design/icons";


class WithdrawalByCategoryIndex extends Component {
    constructor(props) {
        super(props);
        this.state = {
            ...this.stateFromMode('month'),

            selected_category_id_for_modal: null,
            records: [],
        }
    }

    stateFromMode = (mode) => {
        let currentTime = moment();

        if (mode === "month") {
            return ({
                mode: 'month',
                month: currentTime.month(),
                year: currentTime.year(),
                time_from: (currentTime.clone()).startOf('month'),
                time_to: (currentTime.clone()).add(1, 'month').startOf('month'),
            })
        } else if (mode === "day") {
            return ({
                mode: 'day',
                month: currentTime.month(),
                year: currentTime.year(),
                time_from: (currentTime.clone()).startOf('day'),
                time_to: (currentTime.clone()).add(1, 'day').startOf('day'),
            })
        }
        return {}
    }


    componentDidMount() {
        this.fetchData();
    };

    fetchData = () => {
        this.setState({ loading: true })

        axios.get(`/withdrawal-by-category`, {
            params: {
                time_from: this.state.time_from?.format('YYYY-MM-DD'),
                time_to: this.state.time_to?.format('YYYY-MM-DD'),
                month: 1,
            }
        })
            .then(response => {
                this.setState({
                    records: response.data.data
                })
            })
            .finally(() => {
                this.setState({ loading: false })
            })
    };

    stateFromMonthYear = (month, year) => ({
        month: month,
        year: year,
        time_from: moment().set({month: month, year: year}).startOf('month'),
        time_to: moment().set({month: month, year: year}).add(1, 'month').startOf('month')
    });


    render() {
        return (
            <div>
                <Modal
                    width="100%"
                    destroyOnClose={true}
                    open={this.state.selected_category_id_for_modal}
                    onCancel={() => { this.setState({ selected_category_id_for_modal: null }) }}
                    footer={false}
                >
                    { this.state.selected_category_id_for_modal ? (
                        <TransactionIndex
                            type={'Withdrawal'}
                            timeFrom={this.state.time_from}
                            timeTo={this.state.time_to}
                            categoryId={this.state.selected_category_id_for_modal}
                        />
                    ) : null }
                </Modal>


                <Card size="small">
                    {this.state.mode === "day" ? this.renderDayFilter() : null}
                    {this.state.mode === "month" ? (
                        <Space>
                            <Button
                                size="small"
                                onClick={() => {
                                    const month = this.state.month === 0 ? 11 : this.state.month - 1
                                    const year = this.state.month === 0 ? this.state.year - 1 : this.state.year
                                    this.setState(this.stateFromMonthYear(month, year), this.fetchData)
                                }}
                            >
                                Prev
                            </Button>

                            <label>
                                Month - Year :
                            </label>

                            <Select
                                size="small"
                                value={this.state.month}
                                onChange={value => {
                                    this.setState({
                                        month: value
                                    })
                                }}
                                options={_.range(0, 12).map(i => ({
                                    value: i,
                                    label: moment().set({month: i}).format("MMMM")
                                }))}
                            />

                            <Input
                                size="small"
                                type="number"
                                onChange={e => {
                                    this.setState(this.stateFromMonthYear(month, year), this.fetchData)
                                }}
                                value={this.state.year}
                            />

                            <Button
                                size="small"
                                onClick={() => {
                                    const month = this.state.month === 11 ? 0 : this.state.month + 1
                                    const year = this.state.month === 11 ? this.state.year + 1 : this.state.year
                                    this.setState(this.stateFromMonthYear(month, year), this.fetchData)
                                }}
                            >
                                NEXT
                            </Button>
                        </Space>
                    ) : null}

                    <Button
                        type="primary"
                        loading={this.state.loading}
                        onClick={() => {
                            this.fetchData()
                        }}
                        icon={<ReloadOutlined/>}
                    >
                        Reload Data
                    </Button>
                </Card>

                <div>
                    <strong> {this.state.time_from?.format("MMMM Do YYYY")} </strong>
                    to
                    <strong> {this.state.time_to?.format("MMMM Do YYYY")} </strong>
                </div>


                <Card size='small'>
                    <Table
                        size="small"
                        dataSource={this.state.records}
                        rowKey={r => r.category_id}
                        pagination={false}
                        columns={[
                            {key: 'id', title: 'ID', render: r => r.category_id},
                            {key: 'category_name', title: 'Category', render: r => r.category_name},
                            {
                                key: 'total',
                                title: 'Total',
                                align: 'right',
                                render: r => {
                                    return (
                                        <Button
                                            size="small"
                                            onClick={() => {
                                                this.setState({
                                                    selected_category_id_for_modal: r.category_id
                                                })
                                            }}
                                        >
                                            {formatCurrency(r.total)}
                                        </Button>
                                    );
                                }
                            },
                        ]}
                        summary={() => (
                            <>

                                <Table.Summary.Row>
                                    <Table.Summary.Cell index={0}> </Table.Summary.Cell>
                                    <Table.Summary.Cell index={1}> Total: </Table.Summary.Cell>
                                    <Table.Summary.Cell index={2} align="right">
                                        <strong>
                                            {
                                                formatCurrency(this.grandTotal())
                                            }
                                        </strong>
                                    </Table.Summary.Cell>
                                </Table.Summary.Row>
                            </>
                        )

                        }
                    />
                </Card>


            </div>
        );
    }
    grandTotal() {
        return this.state.records.reduce((acc, next) => {
            return acc.add(next.total)
        }, new Decimal(0)).toNumber();
    }

    renderDayFilter() {
        return <>
            <label htmlFor="time_from">
                Time From:
            </label>

            <input
                value={this.state.time_from?.format('YYYY-MM-DD')}
                onChange={e => {
                    this.setState({
                        time_from: moment(e.target.value, "YYYY-MM-DD")
                    })
                }}
                id="time_from"
                type="date"
            />

            <label htmlFor="time_to">
                Time To:
            </label>

            <input
                value={this.state.time_to?.format('YYYY-MM-DD')}
                onChange={e => {
                    this.setState({
                        time_to: moment(e.target.value, "YYYY-MM-DD")
                    })
                }}
                id="time_to"
                type="date"
            />
        </>;
    }

    links() {
    }
}

WithdrawalByCategoryIndex.layout = page => <AppLayout children={page}/>

export default WithdrawalByCategoryIndex;
