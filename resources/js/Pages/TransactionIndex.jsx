import React, {Component} from "react";
import {Button, Card, Flex, Input, Space, Table} from "antd";
import {formatCurrency} from "../utilities.js";
import AppLayout from "./AppLayout.jsx";
import Decimal from "decimal.js";

class TransactionIndex extends Component {
    constructor(props) {
        super(props);
        this.state = {
            type: props.type,
            time_from: props.timeFrom,
            time_to: props.timeTo,
            category_id: props.categoryId,
            account_id: props.accountId,
            account_from_id: props.accountFromId,
            account_to_id: props.accountToId,
            payee_id: props.payee_id,
            records: [],
            sort_column: 'date',
            sort_is_ascending: false,

            current_page: 1,
            per_page: 100,
            total: 0,
        }
    }

    componentDidMount() {
        let params = new URL(document.location.toString()).searchParams;

        this.setState({
            account_id: params.get("account_id")
        }, () => {
            this.fetchData();
        })
    }


    fetchData = () => {
        this.setState({loading: true})

        axios.get(`/transaction`, {
            params: this.fetchParams()
        }).then(response => {
            this.setState({
                records: response.data.data,
                current_page: response.data.meta?.current_page,
                per_page: response.data.meta?.per_page,
                total: response.data.meta?.total,
            })
        }).finally(() => {
            this.setState({loading: false})
        })
    };

    fetchParams = () => ({
        type: this.state.type,
        time_from: this.state.time_from?.format('YYYY-MM-DD'),
        time_to: this.state.time_to?.format('YYYY-MM-DD'),
        category_id: this.state.category_id,
        search: this.state.search,
        sort_column: this.state.sort_column,
        sort_is_ascending: this.state.sort_is_ascending ? 1 : 0,
        account_id: this.state.account_id,
        account_from_id: this.state.account_from_id,
        account_to_id: this.state.account_to_id,
        payee_id: this.state.payee_id,

        page: this.state.current_page,
        per_page: this.state.per_page,
    });

    render() {
        return (
            <Space direction="vertical" style={{width: '100%'}}>
                <Flex gap={"0.5rem"}>
                    <Input.Search
                        style={{flex: 1}}
                        placeholder="Search..."
                        value={this.state.search}
                        onChange={e => {
                            this.setState({search: e.target.value}, this.fetchData)
                        }}
                    />

                    <Button
                        type="primary"
                        loading={this.state.loading}
                        onClick={() => {
                            this.fetchData()
                        }}
                    >
                        Reload Data
                    </Button>
                </Flex>

                <div style={{width: '100%'}}>
                    <Card size='small'>
                        <Table
                            loading={this.state.loading}
                            sticky={true}
                            size="small"
                            pagination={{
                                pageSize: this.state.per_page,
                                current: this.state.current_page,
                                total: this.state.total,
                            }}
                            onRow={record => ({
                                style: {
                                    background: new Decimal(record.relative_amount).gt(0) ?
                                        'rgba(0, 255, 0, 0.2)' :
                                        'rgba(255, 0, 0, 0.2)'
                                }
                            })}
                            dataSource={this.state.records}
                            rowKey={r => r.transaction_id}
                            onChange={(pagination, filters, sorter, extra) => {
                                this.setState({
                                    sort_column: sorter.columnKey,
                                    sort_is_ascending: sorter.order === "ascend",
                                    current_page: pagination.current,
                                    per_page: pagination.pageSize,
                                }, this.fetchData)
                            }}
                            columns={[
                                {key: 'transaction_id', sorter: true, title: 'ID', render: r => r.transaction_id},
                                ...this.state.type === "Withdrawal" ? [
                                        {key: 'account_from', sorter: true, title: 'Account', render: r => r.account_from},
                                    ] :
                                    [
                                        {key: 'account_from', title: 'Account From', render: r => r.account_from},
                                        {key: 'account_to', title: 'Account To', render: r => r.account_to},
                                    ],
                                {
                                    key: 'relative_amount',
                                    title: 'Amount',
                                    align: 'right',
                                    render: r => (
                                        <span style={{
                                            fontFamily: 'monospace',
                                            fontWeight: 'bolder'
                                        }}>
                                            {formatCurrency(r.relative_amount)}
                                        </span>
                                    ),
                                },
                                {key: 'date', title: 'Date', render: r => r.date},
                                {key: 'payee', title: 'Payee', render: r => r.payee},
                                {key: 'notes', title: 'Notes', render: r => r.notes},
                            ].map(col => ({
                                ...col,
                                sorter: true,
                                sortOrder: this.state.sort_column === col.key ?
                                    this.state.sort_is_ascending ? "ascend" : "descend" :
                                    null
                            }))
                            }
                        />
                    </Card>
                </div>
            </Space>

        )
    }
}

TransactionIndex.layout = page => <AppLayout children={page}/>
export default TransactionIndex
