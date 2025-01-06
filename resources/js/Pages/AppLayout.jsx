import React from 'react';
import {Button, Card, Col, Row, Space} from "antd";
import {Link} from "@inertiajs/react";
import {LoadingOutlined} from "@ant-design/icons";

class AppLayout extends React.Component {
    constructor(props) {
        super(props);
        this.state = {

            fetching_accounts: true,
            accounts: [],
        }
    }


    componentDidMount() {
        this.fetchData()
    }

    fetchData = () => {
        this.setState({ fetching_accounts: true })

        axios.get(`/account`)
            .then(response => {
                this.setState({
                    accounts: response.data.data
                })
            })
            .finally(() => {
                this.setState({
                    fetching_accounts: false
                })
            })
    }

    render() {
        return (
            <Row>
                <Col span={6}>
                    <Card size='small'>
                        <Space direction="vertical">
                            <Link href="/withdrawal-by-category/page">
                                <Button> Withdrawal By Category </Button>
                            </Link>

                            <Link href="/transaction/page">
                                <Button> All Transactions </Button>
                            </Link>

                            <Button
                                loading={this.state.fetching_accounts}
                                onClick={() => {
                                    this.fetchData()
                                }}
                                icon={<LoadingOutlined/>}>
                                Reload Accounts
                            </Button>

                            {this.state.accounts.map(a => (
                                <Link key={a.account_id} href={`/transaction/page?account_id=${a.account_id}`}>
                                    <Button style={{ width: '100%' }}>
                                        {a.account_name}
                                    </Button>
                                </Link>
                            ))}


                        </Space>

                    </Card>
                </Col>

                <Col span={18}>
                    <Card size='small'>
                        {this.props.children}
                    </Card>
                </Col>
            </Row>
        )
    }
}

AppLayout.propTypes = {};

export default AppLayout;

